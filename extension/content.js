/**
 * PassVault Companion — Content Script Autofill Engine
 */

chrome.runtime.onMessage.addListener((request, sender, sendResponse) => {
    if (request.action === 'autofill') {
        const { username, password } = request;
        let filled = false;

        // Cari input password pada halaman
        const passInputs = Array.from(document.querySelectorAll('input[type="password"]'));
        if (passInputs.length > 0) {
            const passInput = passInputs[0];
            setValue(passInput, password);

            // Cari input username/email terdekat sebelumnya
            const form = passInput.closest('form');
            const container = form || document;
            const userInputs = Array.from(container.querySelectorAll('input[type="text"], input[type="email"], input:not([type])'))
                .filter(i => i !== passInput && isVisible(i));

            if (userInputs.length > 0) {
                // Prioritaskan input sebelum password
                const userInput = userInputs[userInputs.length - 1];
                setValue(userInput, username);
            }
            filled = true;
        }

        sendResponse({ success: filled });
    }
    return true;
});

function isVisible(elem) {
    return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
}

function setValue(input, value) {
    if (!input || !value) return;
    input.focus();
    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
    input.blur();
}
