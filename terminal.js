// terminal.js

/**
 * Copie dans le presse-papiers le contenu du <pre id="terminal-content">
 */
function copyTerminalContent() {
  const pre = document.getElementById('terminal-content');
  if (!pre) {
    console.error('Élément #terminal-content introuvable');
    return;
  }

  const textToCopy = pre.innerText;

  if (!navigator.clipboard) {
    // Fallback pour anciens navigateurs
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

  // API moderne
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
