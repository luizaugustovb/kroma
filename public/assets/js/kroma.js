/**
 * KROMA PRINT ERP — JavaScript Global
 * Controles de UI, sidebar, dark mode, notificações, AJAX helpers
 */

'use strict';

// ===================================================
// KROMA: Namespace global
// ===================================================
const KROMA = {

    // URL base da aplicação
    baseUrl: document.querySelector('meta[name="base-url"]')?.content || '',

    // ===================================================
    // INICIALIZAÇÃO
    // ===================================================
    init() {
        this.sidebar.init();
        this.notifications.init();
        this.flash.init();
        this.tables.init();
        this.forms.init();
        this.productSearch.init();
        this.tooltips.init();
    },

    // ===================================================
    // SIDEBAR
    // ===================================================
    sidebar: {
        el: null,
        overlay: null,
        collapsed: false,

        init() {
            this.el      = document.querySelector('.sidebar');
            this.overlay = document.querySelector('.sidebar-overlay');
            const toggle = document.querySelector('.topbar-toggle');
            const mainContent = document.querySelector('.main-content');

            if (!this.el) return;

            // Restaura estado salvo
            this.collapsed = localStorage.getItem('sidebar_collapsed') === 'true';
            if (this.collapsed) {
                this.el.classList.add('collapsed');
                mainContent?.classList.add('sidebar-collapsed');
            }

            // Toggle do botão
            toggle?.addEventListener('click', () => this.toggle());

            // Overlay mobile
            this.overlay?.addEventListener('click', () => this.closeMobile());

            // Marca item ativo
            this.markActive();
        },

        toggle() {
            const mainContent = document.querySelector('.main-content');
            const isMobile = window.innerWidth < 992;

            if (isMobile) {
                this.el.classList.toggle('mobile-open');
                this.overlay?.classList.toggle('active');
            } else {
                this.collapsed = !this.collapsed;
                this.el.classList.toggle('collapsed', this.collapsed);
                mainContent?.classList.toggle('sidebar-collapsed', this.collapsed);
                localStorage.setItem('sidebar_collapsed', this.collapsed);
            }
        },

        closeMobile() {
            this.el.classList.remove('mobile-open');
            this.overlay?.classList.remove('active');
        },

        markActive() {
            const path = window.location.pathname;
            document.querySelectorAll('.nav-item').forEach(item => {
                const href = item.getAttribute('href') || '';
                if (href && path.startsWith(href) && href !== '/') {
                    item.classList.add('active');
                } else if (href === path) {
                    item.classList.add('active');
                }
            });
        }
    },

    // ===================================================
    // FLASH MESSAGES
    // ===================================================
    flash: {
        init() {
            document.querySelectorAll('.flash-message').forEach(el => {
                setTimeout(() => {
                    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-10px)';
                    setTimeout(() => el.remove(), 500);
                }, 5000);
            });
        },

        show(message, type = 'info') {
            const icons = {
                success: 'bi-check-circle-fill',
                error:   'bi-x-circle-fill',
                warning: 'bi-exclamation-triangle-fill',
                info:    'bi-info-circle-fill'
            };
            const labels = {
                success: 'Sucesso',
                error:   'Erro',
                warning: 'Atenção',
                info:    'Informação'
            };
            const badgeTypes = {
                success: 'success',
                error:   'danger',
                warning: 'warning',
                info:    'info'
            };

            const el = document.createElement('div');
            el.className = `flash-message flash-${type}`;
            const badge = document.createElement('span');
            badge.className = `badge badge-${badgeTypes[type] || 'info'}`;
            badge.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i> ${labels[type] || labels.info}`;
            const text = document.createElement('span');
            text.className = 'flash-text';
            text.textContent = message;
            el.append(badge, text);

            const container = document.querySelector('.page-content') || document.body;
            container.prepend(el);

            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-10px)';
                setTimeout(() => el.remove(), 500);
            }, 5000);
        }
    },

    // ===================================================
    // NOTIFICAÇÕES
    // ===================================================
    notifications: {
        count: 0,

        init() {
            this.loadCount();
            // Atualiza a cada 60 segundos
            setInterval(() => this.loadCount(), 60000);
        },

        loadCount() {
            fetch(KROMA.baseUrl + '/api/notificacoes/count')
                .then(r => r.json())
                .then(data => {
                    this.count = data.count || 0;
                    const btn = document.querySelector('#notif-btn');
                    let badge = document.querySelector('#notif-badge');
                    if (!badge && btn && this.count > 0) {
                        badge = document.createElement('span');
                        badge.className = 'badge';
                        badge.id = 'notif-badge';
                        btn.appendChild(badge);
                    }
                    if (badge) {
                        badge.textContent = this.count;
                        badge.style.display = this.count > 0 ? 'block' : 'none';
                    }
                })
                .catch(() => {}); // Silencioso
        }
    },

    // ===================================================
    // DATATABLES
    // ===================================================
    tables: {
        init() {
            if (typeof $.fn.DataTable === 'undefined') return;

            document.querySelectorAll('.datatable').forEach(el => {
                const options = JSON.parse(el.dataset.options || '{}');
                $(el).DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                    },
                    pageLength: 25,
                    dom: '<"row mb-3"<"col-sm-6"l><"col-sm-6"f>>rt<"row mt-3"<"col-sm-6"i><"col-sm-6"p>>',
                    responsive: true,
                    ...options
                });
            });
        }
    },

    // ===================================================
    // FORMULÁRIOS
    // ===================================================
    forms: {
        init() {
            // Máscara de CNPJ/CPF
            document.querySelectorAll('[data-mask="cnpj"]').forEach(el => {
                el.addEventListener('input', function() {
                    let v = this.value.replace(/\D/g, '');
                    if (v.length <= 11) {
                        v = v.replace(/(\d{3})(\d)/, '$1.$2')
                             .replace(/(\d{3})(\d)/, '$1.$2')
                             .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    } else {
                        v = v.replace(/(\d{2})(\d)/, '$1.$2')
                             .replace(/(\d{3})(\d)/, '$1.$2')
                             .replace(/(\d{3})(\d)/, '$1/$2')
                             .replace(/(\d{4})(\d{1,2})$/, '$1-$2');
                    }
                    this.value = v;
                });
            });

            // Máscara de telefone
            document.querySelectorAll('[data-mask="telefone"]').forEach(el => {
                el.addEventListener('input', function() {
                    let v = this.value.replace(/\D/g, '').slice(0, 11);
                    if (v.length <= 10) {
                        v = v.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                    } else {
                        v = v.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                    }
                    this.value = v;
                });
            });

            // Máscara de CEP
            document.querySelectorAll('[data-mask="cep"]').forEach(el => {
                el.addEventListener('input', function() {
                    let v = this.value.replace(/\D/g, '').slice(0, 8);
                    v = v.replace(/(\d{5})(\d{0,3})/, '$1-$2');
                    this.value = v;
                });

                el.addEventListener('blur', function() {
                    const cep = this.value.replace(/\D/g, '');
                    if (cep.length === 8) KROMA.forms.buscarCep(cep, el);
                });
            });

            // Submissão com loading
            document.querySelectorAll('form[data-loading]').forEach(form => {
                form.addEventListener('submit', function() {
                    const btn = this.querySelector('[type="submit"]');
                    if (btn) {
                        btn.disabled = true;
                        const original = btn.innerHTML;
                        btn.innerHTML = '<span class="spinner"></span> Aguarde...';
                        btn.dataset.original = original;
                    }
                });
            });
        },

        buscarCep(cep, input) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(r => r.json())
                .then(data => {
                    if (data.erro) return;
                    const form = input.closest('form');
                    const set  = (name, val) => {
                        const el = form?.querySelector(`[name="${name}"]`);
                        if (el) el.value = val;
                    };
                    set('endereco', data.logradouro);
                    set('bairro', data.bairro);
                    set('cidade', data.localidade);
                    set('estado', data.uf);
                })
                .catch(() => {});
        }
    },

    // ===================================================
    // SELECT PESQUISAVEL DE PRODUTOS
    // ===================================================
    productSearch: {
        selector: 'select[data-produto-select], select[data-catalogo-select]',
        observer: null,

        init() {
            this.enhanceAll();

            document.addEventListener('click', event => {
                if (!event.target.closest('.produto-search')) {
                    this.closeAll();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape') this.closeAll();
            });

            this.observer = new MutationObserver(mutations => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (!(node instanceof HTMLElement)) return;
                        if (node.matches?.(this.selector)) this.enhance(node);
                        node.querySelectorAll?.(this.selector).forEach(select => this.enhance(select));
                    });
                });
            });

            this.observer.observe(document.body, { childList: true, subtree: true });
        },

        enhanceAll(root = document) {
            root.querySelectorAll(this.selector).forEach(select => this.enhance(select));
        },

        enhance(select) {
            if (!select || select.dataset.produtoSearchEnhanced === 'true') return;
            select.dataset.produtoSearchEnhanced = 'true';

            const wrapper = document.createElement('div');
            wrapper.className = 'produto-search';
            ['mb-1', 'mb-2', 'mb-3', 'mt-1', 'mt-2', 'mt-3'].forEach(cls => {
                if (select.classList.contains(cls)) {
                    wrapper.classList.add(cls);
                    select.classList.remove(cls);
                }
            });

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'produto-search-button';
            button.innerHTML = '<span class="produto-search-label"></span><i class="bi bi-chevron-down"></i>';

            const panel = document.createElement('div');
            panel.className = 'produto-search-panel';
            panel.hidden = true;

            const input = document.createElement('input');
            input.type = 'search';
            input.className = 'produto-search-input';
            input.placeholder = 'Digite para filtrar...';
            input.autocomplete = 'off';

            const list = document.createElement('div');
            list.className = 'produto-search-list';

            const empty = document.createElement('div');
            empty.className = 'produto-search-empty';
            empty.textContent = 'Nenhum produto encontrado';
            empty.hidden = true;

            panel.append(input, list, empty);
            select.parentNode.insertBefore(wrapper, select);
            wrapper.append(select, button, panel);

            const updateLabel = () => {
                const selected = select.selectedOptions[0];
                const text = selected?.textContent?.trim() || '-- Selecionar produto --';
                button.querySelector('.produto-search-label').textContent = text;
                button.classList.toggle('is-empty', !select.value);
            };

            const render = () => {
                const termo = this.normalize(input.value);
                const options = Array.from(select.options).filter(option => {
                    if (option.disabled) return false;
                    if (!termo) return true;
                    const haystack = this.normalize([
                        option.textContent,
                        option.dataset.nome,
                        option.dataset.codigo
                    ].filter(Boolean).join(' '));
                    return haystack.includes(termo);
                });

                list.replaceChildren();
                empty.hidden = options.length > 0;

                options.forEach(option => {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'produto-search-option';
                    item.textContent = option.textContent.trim();
                    item.dataset.value = option.value;
                    item.classList.toggle('is-selected', option.value === select.value);
                    item.addEventListener('click', () => {
                        select.value = option.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        updateLabel();
                        panel.hidden = true;
                        wrapper.classList.remove('is-open');
                        button.focus();
                    });
                    list.appendChild(item);
                });
            };

            const open = () => {
                this.closeAll(wrapper);
                panel.hidden = false;
                wrapper.classList.add('is-open');
                input.value = '';
                render();
                requestAnimationFrame(() => input.focus());
            };

            button.addEventListener('click', () => {
                if (panel.hidden) {
                    open();
                } else {
                    panel.hidden = true;
                    wrapper.classList.remove('is-open');
                }
            });

            input.addEventListener('input', render);
            input.addEventListener('keydown', event => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    list.querySelector('.produto-search-option')?.click();
                }
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    list.querySelector('.produto-search-option')?.focus();
                }
            });

            list.addEventListener('keydown', event => {
                const current = event.target.closest('.produto-search-option');
                if (!current) return;
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    (current.nextElementSibling || list.firstElementChild)?.focus();
                }
                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    (current.previousElementSibling || input)?.focus();
                }
                if (event.key === 'Escape') {
                    event.preventDefault();
                    panel.hidden = true;
                    wrapper.classList.remove('is-open');
                    button.focus();
                }
            });

            select.addEventListener('change', updateLabel);
            updateLabel();
        },

        closeAll(except = null) {
            document.querySelectorAll('.produto-search.is-open').forEach(wrapper => {
                if (wrapper === except) return;
                wrapper.classList.remove('is-open');
                const panel = wrapper.querySelector('.produto-search-panel');
                if (panel) panel.hidden = true;
            });
        },

        normalize(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }
    },

    // ===================================================
    // TOOLTIPS
    // ===================================================
    tooltips: {
        init() {
            if (typeof bootstrap !== 'undefined') {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    new bootstrap.Tooltip(el, { trigger: 'hover' });
                });
            }
        }
    },

    // ===================================================
    // KANBAN (Drag & Drop)
    // ===================================================
    kanban: {
        dragEl: null,

        init() {
            const cards = document.querySelectorAll('.kanban-card');
            const zones = document.querySelectorAll('.kanban-cards');

            cards.forEach(card => {
                card.setAttribute('draggable', true);

                card.addEventListener('dragstart', e => {
                    this.dragEl = card;
                    card.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', card.dataset.id);
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('dragging');
                    document.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
                });
            });

            zones.forEach(zone => {
                zone.addEventListener('dragover', e => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    zone.classList.add('drag-over');
                });

                zone.addEventListener('dragleave', () => {
                    zone.classList.remove('drag-over');
                });

                zone.addEventListener('drop', e => {
                    e.preventDefault();
                    zone.classList.remove('drag-over');

                    if (this.dragEl) {
                        const leadId   = this.dragEl.dataset.id;
                        const novoEstagio = zone.dataset.estagio;
                        const colOld   = this.dragEl.closest('.kanban-column');
                        const colNew   = zone.closest('.kanban-column');

                        zone.appendChild(this.dragEl);

                        // Atualiza contadores
                        this.atualizarContador(colOld);
                        this.atualizarContador(colNew);

                        // Persiste via AJAX
                        this.moverLead(leadId, novoEstagio);
                    }
                });
            });
        },

        atualizarContador(col) {
            if (!col) return;
            const count = col.querySelectorAll('.kanban-card').length;
            const badge = col.querySelector('.kanban-count');
            if (badge) badge.textContent = count;
        },

        moverLead(leadId, estagio) {
            fetch(KROMA.baseUrl + `/crm/leads/${leadId}/mover`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ estagio })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    KROMA.flash.show('Erro ao mover lead: ' + data.message, 'error');
                }
            })
            .catch(() => KROMA.flash.show('Erro de conexão', 'error'));
        }
    },

    // ===================================================
    // HELPERS AJAX
    // ===================================================
    ajax: {
        get(url) {
            return fetch(KROMA.baseUrl + url, {
                headers: { 'Accept': 'application/json' }
            }).then(r => r.json());
        },

        post(url, data) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            return fetch(KROMA.baseUrl + url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrf
                },
                body: JSON.stringify(data)
            }).then(r => r.json());
        }
    },

    // ===================================================
    // FORMATAÇÃO
    // ===================================================
    format: {
        moeda(valor) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor || 0);
        },

        data(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('pt-BR');
        },

        numero(n) {
            return new Intl.NumberFormat('pt-BR').format(n || 0);
        }
    }
};

// Inicializa quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => KROMA.init());
