document.addEventListener('DOMContentLoaded', () => {
    initializeLiveComponents();
});

let debounceTimers = {};

document.addEventListener('click', e => {
    const el = e.target.closest('[ph-click]');
    if (!el) return;
    const component = el.closest('[data-live-component]');
    if (!component) return;

    const rawAction = el.getAttribute('ph-click');
    const { action, params } = parseAction(rawAction);
    
    updateLiveComponent(component, action, params);
});

document.addEventListener('input', e => {
    const el = e.target.closest('[ph-model]');
    if (!el) return;
    const component = el.closest('[data-live-component]');
    if (!component) return;

    const property = el.getAttribute('ph-model');
    const isDebounced = el.hasAttribute('ph-debounce');
    const delay = el.getAttribute('ph-debounce') || 300;

    if (el.type !== 'file') {
        const update = () => {
            const state = JSON.parse(atob(component.getAttribute('data-live-state')));
            state[property] = el.value;
            component.setAttribute('data-live-state', btoa(JSON.stringify(state)));
            
            // If it has URL sync, update the URL
            syncUrl(component, property, el.value);
            
            // Only trigger server update if it's not just a state change, 
            // but for simplicity in Phantom, we update on every model change.
            updateLiveComponent(component);
        };

        if (isDebounced) {
            clearTimeout(debounceTimers[property]);
            debounceTimers[property] = setTimeout(update, delay);
        } else {
            update();
        }
    }
});

function parseAction(raw) {
    const match = raw.match(/([^(]+)(?:\((.*)\))?/);
    if (!match) return { action: raw, params: [] };
    
    const action = match[1];
    const paramsRaw = match[2] ? match[2].split(',').map(p => p.trim().replace(/['"]/g, '')) : [];
    
    // Simple type conversion
    const params = paramsRaw.map(p => isNaN(p) ? p : (p.includes('.') ? parseFloat(p) : parseInt(p)));
    
    return { action, params };
}

function syncUrl(component, property, value) {
    const urlSync = JSON.parse(atob(component.getAttribute('data-live-url-sync') || 'W10='));
    if (urlSync.includes(property)) {
        const url = new URL(window.location);
        if (value) url.searchParams.set(property, value);
        else url.searchParams.delete(property);
        window.history.replaceState({}, '', url);
    }
}

function initializeLiveComponents() {
    document.querySelectorAll('[data-live-component]').forEach(component => {
        const poll = component.querySelector('[ph-poll]');
        if (poll && !component.dataset.pollingSet) {
            const ms = poll.getAttribute('ph-poll') || 5000;
            setInterval(() => updateLiveComponent(component), ms);
            component.dataset.pollingSet = true;
        }
    });
}

window.PhantomLive = {
    emit(event, params = []) {
        document.querySelectorAll('[data-live-component]').forEach(component => {
            const listeners = JSON.parse(atob(component.getAttribute('data-live-listeners') || 'e30='));
            if (listeners[event]) {
                updateLiveComponent(component, listeners[event], params);
            }
        });
    }
};

async function updateLiveComponent(component, action = null, params = []) {
    const name = component.getAttribute('data-live-component');
    const id = component.getAttribute('data-live-id');
    const state = component.getAttribute('data-live-state');

    const loaders = component.querySelectorAll('[ph-loading]');
    loaders.forEach(l => l.style.display = 'block');

    try {
        const formData = new FormData();
        formData.append('component', name);
        formData.append('id', id);
        formData.append('state', state);
        if (action) formData.append('action', action);
        if (params.length) formData.append('params', JSON.stringify(params));

        component.querySelectorAll('input[type="file"][ph-model]').forEach(fileInput => {
            if (fileInput.files.length > 0) {
                formData.append(fileInput.getAttribute('ph-model'), fileInput.files[0]);
            }
        });

        const response = await fetch('/phantom/live/update', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });

        const data = await response.json();
        if (data.error) return console.error('Phantom Live Error:', data.error);
        if (data.redirect) return window.location.href = data.redirect;

        const parser = new DOMParser();
        const doc = parser.parseFromString(data.html, 'text/html');
        const newNode = doc.querySelector('[data-live-component]');
        
        morph(component, newNode);
        component.setAttribute('data-live-state', data.state);
        
        if (data.events) {
            data.events.forEach(e => window.PhantomLive.emit(e.event, e.params));
        }
        
    } catch (err) {
        console.error('Phantom Live Fetch Error:', err);
    } finally {
        loaders.forEach(l => l.style.display = 'none');
    }
}

function morph(oldNode, newNode) {
    if (oldNode.nodeName !== newNode.nodeName) {
        oldNode.replaceWith(newNode.cloneNode(true));
        return;
    }

    const oldAttrs = oldNode.attributes;
    const newAttrs = newNode.attributes;
    for (const attr of newAttrs) {
        if (oldNode.getAttribute(attr.name) !== attr.value) oldNode.setAttribute(attr.name, attr.value);
    }
    for (const attr of oldAttrs) {
        if (!newNode.hasAttribute(attr.name)) oldNode.removeAttribute(attr.name);
    }

    if (newNode.childNodes.length === 0 || (newNode.childNodes.length === 1 && newNode.childNodes[0].nodeType === Node.TEXT_NODE)) {
        if (oldNode.textContent !== newNode.textContent) {
            if (oldNode.nodeName === 'INPUT' || oldNode.nodeName === 'TEXTAREA') {
                if (document.activeElement !== oldNode) oldNode.value = newNode.value;
            } else {
                oldNode.textContent = newNode.textContent;
            }
        }
        return;
    }

    const oldChildren = Array.from(oldNode.childNodes);
    const newChildren = Array.from(newNode.childNodes);
    newChildren.forEach((newChild, i) => {
        const oldChild = oldChildren[i];
        if (!oldChild) oldNode.appendChild(newChild.cloneNode(true));
        else morph(oldChild, newChild);
    });
    while (oldNode.childNodes.length > newChildren.length) oldNode.removeChild(oldNode.lastChild);
}