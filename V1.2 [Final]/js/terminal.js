// terminal.js

/**
 * Copies the contents of the <pre id=“terminal-content”> to the clipboard.
 */
function copyTerminalContent() {
  const pre = document.getElementById('terminal-content');
  if (!pre) {
    console.error('Élément #terminal-content introuvable');
    return;
  }

  const textToCopy = pre.innerText;

  if (!navigator.clipboard) {
    // Fallback for older browsers
    const range = document.createRange();
    range.selectNodeContents(pre);
    const sel = window.getSelection();
    sel.removeAllRanges();
    sel.addRange(range);
    try {
      document.execCommand('copy');
      sel.removeAllRanges();
    } catch (err) {
      console.error('Erreur de copie (execCommand) :', err);
    }
    return;
  }

  // Modern API
  navigator.clipboard.writeText(textToCopy)
    .then(() => {
      const btn = document.querySelector('.terminal-copy-btn');
      const original = btn.innerText;
      btn.innerText = 'Copié !';
      setTimeout(() => {
        btn.innerText = original;
      }, 1000);
    })
    .catch((err) => {
      console.error('Erreur de copie (Clipboard API) :', err);
    });
}
