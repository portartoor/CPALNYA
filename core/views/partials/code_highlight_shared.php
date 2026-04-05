<style>
:root {
    /* Classic palette (default / apigeo-like) */
    --hl-json-key: #c678dd;
    --hl-json-string: #98c379;
    --hl-json-number: #d19a66;
    --hl-json-boolean: #56b6c2;
    --hl-json-null: #e06c75;
    --hl-json-shadow: none;

    --hl-bash-command: #61afef;
    --hl-bash-flag: #d19a66;
    --hl-bash-string: #98c379;
    --hl-bash-comment: #7f8c98;
}

/* Geo theme keeps brighter/neon-like accenting */
.geo-docs,
.geo-theme {
    --hl-json-key: #ff6ec7;
    --hl-json-string: #00ffea;
    --hl-json-number: #ffd700;
    --hl-json-boolean: #ff3d81;
    --hl-json-null: #89ddff;
    --hl-json-shadow: 0 0 1px currentColor;

    --hl-bash-command: #c792ea;
    --hl-bash-flag: #ffcb6b;
    --hl-bash-string: #c3e88d;
    --hl-bash-comment: #7f8c98;
}

.json-key, .json-key2 { color: var(--hl-json-key); text-shadow: var(--hl-json-shadow); }
.json-string { color: var(--hl-json-string); text-shadow: var(--hl-json-shadow); }
.json-number { color: var(--hl-json-number); text-shadow: var(--hl-json-shadow); }
.json-boolean { color: var(--hl-json-boolean); text-shadow: var(--hl-json-shadow); }
.json-null { color: var(--hl-json-null); text-shadow: var(--hl-json-shadow); }

.bash-command { color: var(--hl-bash-command); font-weight: 700; }
.bash-flag { color: var(--hl-bash-flag); }
.bash-string { color: var(--hl-bash-string); }
.bash-comment { color: var(--hl-bash-comment); font-style: italic; }

.code-comment { color: #7f8c98; font-style: italic; }
.code-string { color: #98c379; }
.code-number { color: #d19a66; }
.code-keyword { color: #c678dd; font-weight: 700; }
.code-function { color: #61afef; }
.code-variable { color: #e5c07b; }

/* Language-specific differentiation */
pre.javascript .code-keyword, pre.js .code-keyword, pre.language-javascript .code-keyword, pre.language-js .code-keyword,
code.javascript .code-keyword, code.js .code-keyword, code.language-javascript .code-keyword, code.language-js .code-keyword { color: #61afef; }
pre.javascript .code-function, pre.js .code-function, pre.language-javascript .code-function, pre.language-js .code-function,
code.javascript .code-function, code.js .code-function, code.language-javascript .code-function, code.language-js .code-function { color: #56b6c2; }

pre.php .code-keyword, pre.language-php .code-keyword,
code.php .code-keyword, code.language-php .code-keyword { color: #c678dd; }
pre.php .code-variable, pre.language-php .code-variable,
code.php .code-variable, code.language-php .code-variable { color: #e5c07b; }

pre.python .code-keyword, pre.language-python .code-keyword,
code.python .code-keyword, code.language-python .code-keyword { color: #e5c07b; }
pre.python .code-function, pre.language-python .code-function,
code.python .code-function, code.language-python .code-function { color: #61afef; }

pre.java .code-keyword, pre.language-java .code-keyword,
code.java .code-keyword, code.language-java .code-keyword { color: #e06c75; }
pre.cpp .code-keyword, pre.language-cpp .code-keyword,
code.cpp .code-keyword, code.language-cpp .code-keyword { color: #56b6c2; }
pre.kotlin .code-keyword, pre.language-kotlin .code-keyword,
code.kotlin .code-keyword, code.language-kotlin .code-keyword { color: #d19a66; }
</style>
<script>
(function () {
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function syntaxHighlightJsonText(raw) {
        let obj;
        try {
            obj = JSON.parse(raw);
        } catch (e) {
            return null;
        }
        const pretty = JSON.stringify(obj, null, 2);
        return escapeHtml(pretty).replace(
            /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g,
            function (match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    cls = /:$/.test(match) ? 'json-key' : 'json-string';
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            }
        );
    }

    function highlightJsonElement(el) {
        if (!el || el.dataset.geoipCodeHighlightedJson === '1') return;
        const raw = el.textContent || '';
        const html = syntaxHighlightJsonText(raw);
        if (html === null) return;
        el.innerHTML = html;
        el.dataset.geoipCodeHighlightedJson = '1';
    }

    function highlightBashElement(el) {
        if (!el || el.dataset.geoipCodeHighlightedBash === '1') return;
        const lines = (el.textContent || '').split('\n');
        const html = lines.map(function (line) {
            if (line.trim().startsWith('#')) {
                return '<span class="bash-comment">' + escapeHtml(line) + '</span>';
            }
            let out = escapeHtml(line);
            out = out.replace(/("[^"]*"|'[^']*')/g, '<span class="bash-string">$1</span>');
            out = out.replace(/(\s--?\w[\w-]*)/g, '<span class="bash-flag">$1</span>');
            out = out.replace(/^(\w+)/, '<span class="bash-command">$1</span>');
            return out;
        }).join('\n');
        el.innerHTML = html;
        el.dataset.geoipCodeHighlightedBash = '1';
    }

    function applyCodeReplacements(input, replacements) {
        return replacements.reduce(function (acc, item) {
            return acc.replace(item.re, item.to);
        }, input);
    }

    function highlightJsLikeElement(el, lang) {
        const marker = 'geoipCodeHighlighted' + String(lang || 'js');
        if (!el || el.dataset[marker] === '1') return;
        const escaped = escapeHtml(el.textContent || '');
        const out = applyCodeReplacements(escaped, [
            { re: /(\/\/.*)$/gm, to: '<span class="code-comment">$1</span>' },
            { re: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|`(?:\\.|[^`\\])*`)/g, to: '<span class="code-string">$1</span>' },
            { re: /\b(\d+(?:\.\d+)?)\b/g, to: '<span class="code-number">$1</span>' },
            { re: /\b(const|let|var|function|return|if|else|for|while|try|catch|async|await|new|class|import|from|export|true|false|null|undefined)\b/g, to: '<span class="code-keyword">$1</span>' },
            { re: /\b([A-Za-z_]\w*)\s*(?=\()/g, to: '<span class="code-function">$1</span>' }
        ]);
        el.innerHTML = out;
        el.dataset[marker] = '1';
    }

    function highlightPhpElement(el) {
        if (!el || el.dataset.geoipCodeHighlightedPhp === '1') return;
        const escaped = escapeHtml(el.textContent || '');
        const out = applyCodeReplacements(escaped, [
            { re: /(#.*|\/\/.*)$/gm, to: '<span class="code-comment">$1</span>' },
            { re: /('(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*")/g, to: '<span class="code-string">$1</span>' },
            { re: /\b(\d+(?:\.\d+)?)\b/g, to: '<span class="code-number">$1</span>' },
            { re: /(\$[A-Za-z_]\w*)/g, to: '<span class="code-variable">$1</span>' },
            { re: /\b(function|return|if|else|foreach|for|while|new|class|public|private|protected|static|array|true|false|null)\b/g, to: '<span class="code-keyword">$1</span>' },
            { re: /\b([A-Za-z_]\w*)\s*(?=\()/g, to: '<span class="code-function">$1</span>' }
        ]);
        el.innerHTML = out;
        el.dataset.geoipCodeHighlightedPhp = '1';
    }

    function guessLanguageByText(text) {
        const t = String(text || '').trim();
        if (t === '') return '';
        if (t[0] === '{' || t[0] === '[') return 'json';
        if (/^\$[A-Za-z_]\w*\s*=|^<\?php\b/.test(t)) return 'php';
        if (/^(const|let|var)\s|\bfetch\(|=>/.test(t)) return 'javascript';
        if (/^curl\s|\s--data-urlencode\s|\s-H\s/.test(t)) return 'bash';
        return '';
    }

    function normalizeLanguage(el) {
        const cls = el.className || '';
        const m = cls.match(/language-([a-z0-9_+-]+)/i);
        if (m) return m[1].toLowerCase();
        const direct = cls.match(/\b(json|bash|sh|shell|javascript|js|php|python|java|cpp|c\+\+|kotlin)\b/i);
        if (direct) return direct[1].toLowerCase();
        return guessLanguageByText(el.textContent || '');
    }

    function applyToElement(el) {
        const lang = normalizeLanguage(el);
        if (lang === 'json') {
            highlightJsonElement(el);
        } else if (lang === 'bash' || lang === 'sh' || lang === 'shell') {
            highlightBashElement(el);
        } else if (lang === 'javascript' || lang === 'js') {
            highlightJsLikeElement(el, 'js');
        } else if (lang === 'php') {
            highlightPhpElement(el);
        } else if (lang === 'python' || lang === 'java' || lang === 'cpp' || lang === 'c++' || lang === 'kotlin') {
            highlightJsLikeElement(el, lang);
        }
    }

    function applyTo(root) {
        const scope = root || document;
        const list = [];
        scope.querySelectorAll('pre.json, pre.collapsible, pre.bash, pre.javascript, pre.js, pre.php, pre.python, pre.java, pre.cpp, pre.kotlin, pre.code-line, pre.code-block').forEach(function (el) {
            list.push(el);
        });
        scope.querySelectorAll('pre code[class*="language-"]').forEach(function (code) {
            list.push(code);
        });
        list.forEach(applyToElement);
    }

    window.GEOIPCodeHighlight = {
        applyTo: applyTo,
        applyToElement: applyToElement
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { applyTo(document); });
    } else {
        applyTo(document);
    }
})();
</script>
<script>
(function () {
    function hasLookupRequest() {
        try {
            var params = new URLSearchParams(window.location.search || '');
            if (params.has('lookup_ip')) {
                return true;
            }
        } catch (e) {}

        var hash = String(window.location.hash || '').toLowerCase();
        return hash === '#live-response' || hash === '#live-lookup';
    }

    function findLookupTarget() {
        var hash = String(window.location.hash || '');
        if ((hash === '#live-response' || hash === '#live-lookup') && document.querySelector(hash)) {
            return document.querySelector(hash);
        }

        var direct = document.querySelector('#live-response, #live-lookup');
        if (direct) {
            return direct;
        }

        var input = document.querySelector('form input[name="lookup_ip"]');
        if (!input) {
            return null;
        }
        var form = input.closest('form');
        if (!form) {
            return input;
        }
        return form.closest('.lookup-panel, .lookup-wrap, article, .tile, .card, section') || form;
    }

    function fixedHeaderOffset() {
        var candidates = document.querySelectorAll('header, .site-header, .docs-header, .navbar, .topbar');
        var max = 0;
        candidates.forEach(function (el) {
            var style = window.getComputedStyle(el);
            if (!style) return;
            if (style.position !== 'fixed' && style.position !== 'sticky') return;
            var rect = el.getBoundingClientRect();
            if (rect.height > max) {
                max = rect.height;
            }
        });
        return max > 0 ? Math.ceil(max) + 12 : 16;
    }

    function scrollLookupIntoView() {
        if (!hasLookupRequest()) {
            return;
        }
        var target = findLookupTarget();
        if (!target) {
            return;
        }
        var top = window.pageYOffset + target.getBoundingClientRect().top - fixedHeaderOffset();
        window.scrollTo({
            top: Math.max(0, top),
            behavior: 'smooth'
        });
    }

    if (document.readyState === 'complete') {
        setTimeout(scrollLookupIntoView, 80);
    } else {
        window.addEventListener('load', function () {
            setTimeout(scrollLookupIntoView, 80);
        }, { once: true });
    }
})();
</script>
