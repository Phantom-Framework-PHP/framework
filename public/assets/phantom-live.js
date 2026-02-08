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

    // Show loading states
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

        // Patch DOM
        const parser = new DOMParser();
        const doc = parser.parseFromString(data.html, 'text/html');
        const newContent = doc.querySelector('[data-live-component]');
        
        component.replaceWith(newContent);
        
    } catch (err) {
        console.error('Phantom Live Fetch Error:', err);
    } finally {
        // Hide loading states (they will be hidden anyway if component is replaced, 
        // but good to have for safety or partial updates)
        loaders.forEach(l => l.style.display = 'none');
    }
}