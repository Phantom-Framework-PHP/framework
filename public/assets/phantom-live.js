document.addEventListener('click', e => {
    const el = e.target.closest('[ph-click]');
    if (!el) return;

    const component = el.closest('[data-live-component]');
    if (!component) return;

    const action = el.getAttribute('ph-click');
    updateLiveComponent(component, action);
});

document.addEventListener('input', e => {
    const el = e.target.closest('[ph-model]');
    if (!el) return;

    const component = el.closest('[data-live-component]');
    if (!component) return;

    const property = el.getAttribute('ph-model');
    const state = JSON.parse(atob(component.getAttribute('data-live-state')));
    state[property] = el.value;
    component.setAttribute('data-live-state', btoa(JSON.stringify(state)));
});

async function updateLiveComponent(component, action = null) {
    const name = component.getAttribute('data-live-component');
    const id = component.getAttribute('data-live-id');
    const state = component.getAttribute('data-live-state');

    const loaders = component.querySelectorAll('[ph-loading]');
    loaders.forEach(l => l.style.display = 'block');

    try {
        const response = await fetch('/phantom/live/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ component: name, id, state, action })
        });

        const data = await response.json();
        
        if (data.error) {
            console.error('Phantom Live Error:', data.error);
            return;
        }

        const parser = new DOMParser();
        const doc = parser.parseFromString(data.html, 'text/html');
        const newNode = doc.querySelector('[data-live-component]');
        
        // Use Morphing instead of replaceWith
        morph(component, newNode);
        
        // Update state metadata
        component.setAttribute('data-live-state', data.state);
        
    } catch (err) {
        console.error('Phantom Live Fetch Error:', err);
    } finally {
        loaders.forEach(l => l.style.display = 'none');
    }
}

/**
 * Lightweight DOM Morphing Algorithm
 */
function morph(oldNode, newNode) {
    // 1. If nodes are different types, replace entirely
    if (oldNode.nodeName !== newNode.nodeName) {
        oldNode.replaceWith(newNode.cloneNode(true));
        return;
    }

    // 2. Update Attributes
    const oldAttrs = oldNode.attributes;
    const newAttrs = newNode.attributes;

    for (const attr of newAttrs) {
        if (oldNode.getAttribute(attr.name) !== attr.value) {
            oldNode.setAttribute(attr.name, attr.value);
        }
    }
    for (const attr of oldAttrs) {
        if (!newNode.hasAttribute(attr.name)) {
            oldNode.removeAttribute(attr.name);
        }
    }

    // 3. Update Content (Text Nodes)
    if (newNode.childNodes.length === 0 || (newNode.childNodes.length === 1 && newNode.childNodes[0].nodeType === Node.TEXT_NODE)) {
        if (oldNode.textContent !== newNode.textContent) {
            // Preserve focus for inputs
            if (oldNode.nodeName === 'INPUT' || oldNode.nodeName === 'TEXTAREA') {
                if (document.activeElement !== oldNode) {
                    oldNode.value = newNode.value;
                }
            } else {
                oldNode.textContent = newNode.textContent;
            }
        }
        return;
    }

    // 4. Update Children (Recursive)
    const oldChildren = Array.from(oldNode.childNodes);
    const newChildren = Array.from(newNode.childNodes);

    newChildren.forEach((newChild, i) => {
        const oldChild = oldChildren[i];
        if (!oldChild) {
            oldNode.appendChild(newChild.cloneNode(true));
        } else {
            morph(oldChild, newChild);
        }
    });

    // Remove extra old children
    while (oldNode.childNodes.length > newChildren.length) {
        oldNode.removeChild(oldNode.lastChild);
    }
}
