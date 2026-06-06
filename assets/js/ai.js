// assets/js/ai.js
// Handles all Claude AI interactions in the editor.

function getEditorPlainText() {
    if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
        return tinymce.get('content').getContent({ format: 'text' }).trim();
    }
    const el = document.getElementById('content');
    return el ? el.value.trim() : '';
}

function plainTextToHtml(text) {
    const escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    const paragraphs = escaped.split(/\n{2,}/);
    if (paragraphs.length === 1) {
        return '<p>' + escaped.replace(/\n/g, '<br>') + '</p>';
    }
    return paragraphs
        .map(function (p) {
            return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
        })
        .join('');
}

function setEditorContent(text) {
    const html = plainTextToHtml(text);
    if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
        tinymce.get('content').setContent(html);
        tinymce.get('content').save();
    } else {
        const el = document.getElementById('content');
        if (el) el.value = text;
    }
}

async function aiAction(action) {
    const content   = getEditorPlainText();
    const title     = document.querySelector('input[name="title"]').value;
    const spinner   = document.getElementById('spinner');
    const result    = document.getElementById('ai-result');
    const applyWrap = document.getElementById('apply-wrap');

    if (action !== 'generate' && !content) {
        alert('Please write some content first.');
        return;
    }

    spinner.classList.add('visible');
    result.classList.remove('visible');
    applyWrap.style.display = 'none';
    result.textContent = '';

    try {
        const response = await fetch('/ai-site-manager/api/ai-assist.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ action, content, title, page_id: PAGE_ID })
        });

        const data = await response.json();

        if (data.success) {
            result.textContent = data.content;
            result.classList.add('visible');
            applyWrap.style.display = 'block';
        } else {
            result.textContent = 'Error: ' + (data.error || 'Unknown error');
            result.classList.add('visible');
        }
    } catch (err) {
        result.textContent = 'Network error: ' + err.message;
        result.classList.add('visible');
    } finally {
        spinner.classList.remove('visible');
    }
}

function applyResult() {
    const result = document.getElementById('ai-result');
    if (result.textContent) {
        setEditorContent(result.textContent);
        document.getElementById('apply-wrap').style.display = 'none';
        document.getElementById('ai-result').classList.remove('visible');
    }
}
