// Rebuild DOM to support letter-by-letter glow effects on hover globally
document.addEventListener("DOMContentLoaded", () => {
    function wrapTextNodes(element) {
        // Ignore script, style, visual media and input elements
        const ignoredTags = ['SCRIPT', 'STYLE', 'SVG', 'IMG', 'INPUT', 'TEXTAREA', 'BUTTON', 'IFRAME', 'NOSCRIPT', 'OPTION', 'SELECT', 'A', 'NAV'];
        if (ignoredTags.includes(element.tagName)) {
            return;
        }

        // Avoid wrapping interactive components like the EVE robot, tooltips, and floating button
        if (element.classList && (
            element.classList.contains('glow-char') || 
            element.classList.contains('glow-char-space') || 
            element.classList.contains('glow-text-container') ||
            element.closest('.eve-robot') || 
            element.closest('.whatsapp-tooltip') || 
            element.closest('.whatsapp-float')
        )) {
            return;
        }

        const childNodes = Array.from(element.childNodes);
        for (let i = 0; i < childNodes.length; i++) {
            const node = childNodes[i];

            if (node.nodeType === Node.TEXT_NODE) {
                const text = node.textContent;
                // Skip empty or purely whitespace nodes
                if (text.replace(/\s/g, '') === '') {
                    continue;
                }

                const spanContainer = document.createElement('span');
                spanContainer.className = 'glow-text-container';

                for (let j = 0; j < text.length; j++) {
                    const char = text[j];
                    if (char === '\n' || char === '\r' || char === '\t') {
                        spanContainer.appendChild(document.createTextNode(char));
                    } else if (char === ' ') {
                        // Usar espacio estándar para permitir que el navegador rompa líneas de forma responsiva
                        spanContainer.appendChild(document.createTextNode(' '));
                    } else {
                        const charSpan = document.createElement('span');
                        charSpan.className = 'glow-char';
                        charSpan.textContent = char;
                        spanContainer.appendChild(charSpan);
                    }
                }

                element.replaceChild(spanContainer, node);
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                wrapTextNodes(node);
            }
        }
    }

    // Target the main wrapper or body
    const contentBody = document.querySelector('body');
    if (contentBody) {
        wrapTextNodes(contentBody);
    }

    // Hide WhatsApp/EVE container when overlapping with the footer
    const whatsappContainer = document.querySelector('.whatsapp-container');
    const footer = document.querySelector('footer');
    if (whatsappContainer && footer) {
        whatsappContainer.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        
        const checkScroll = () => {
            const scrollPosition = window.innerHeight + window.pageYOffset;
            const footerTop = footer.getBoundingClientRect().top + window.pageYOffset;
            
            if (scrollPosition > footerTop) {
                whatsappContainer.style.opacity = '0';
                whatsappContainer.style.pointerEvents = 'none';
                whatsappContainer.style.transform = 'translateY(20px)';
            } else {
                whatsappContainer.style.opacity = '1';
                whatsappContainer.style.pointerEvents = '';
                whatsappContainer.style.transform = 'translateY(0)';
            }
        };

        window.addEventListener('scroll', checkScroll);
        window.addEventListener('resize', checkScroll);
        checkScroll(); // Initial check
    }
});
