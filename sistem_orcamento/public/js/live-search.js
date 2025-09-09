/**
 * Live Search JavaScript
 * Implementa pesquisa dinâmica nas páginas de índice
 */

class LiveSearch {
    constructor(options = {}) {
        this.searchInput = options.searchInput || document.getElementById('search');
        this.searchForm = options.searchForm || this.searchInput?.closest('form');
        this.resultsContainer = options.resultsContainer || this.getResultsContainer();
        this.debounceDelay = options.debounceDelay || 500;
        this.minLength = options.minLength || 2;
        this.loadingClass = options.loadingClass || 'loading';
        
        this.debounceTimer = null;
        this.currentRequest = null;
        
        this.init();
    }
    
    init() {
        if (!this.searchInput || !this.searchForm) {
            return;
        }
        
        this.wrapInputWithContainer();
        this.setupEventListeners();
    }
    
    getResultsContainer() {
        // Para o dashboard, procurar especificamente o card-body que contém a tabela
        if (window.location.pathname === '/' || window.location.pathname.includes('dashboard')) {
            // Procurar o card-body que contém uma table-responsive
            const cardBodies = document.querySelectorAll('.card-body');
            for (let cardBody of cardBodies) {
                if (cardBody.querySelector('.table-responsive')) {
                    return cardBody;
                }
            }
            // Se não encontrar, procurar pelo card que contém "Últimos Orçamentos"
            const headers = document.querySelectorAll('.card-header h5');
            for (let header of headers) {
                if (header.textContent.includes('Últimos Orçamentos')) {
                    return header.closest('.card').querySelector('.card-body');
                }
            }
        }
        
        // Para outras páginas, usar o seletor padrão
        return document.querySelector('.table-responsive, .card-body');
    }

    wrapInputWithContainer() {
        // Criar container para o input e botão
        const container = document.createElement('div');
        container.className = 'search-container';
        
        // Inserir container antes do input
        this.searchInput.parentNode.insertBefore(container, this.searchInput);
        
        // Mover input para dentro do container
        container.appendChild(this.searchInput);
        
        // Criar botão de limpar
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'search-clear-btn';
        clearBtn.innerHTML = '×';
        clearBtn.title = 'Limpar pesquisa';
        
        // Adicionar botão ao container
        container.appendChild(clearBtn);
        
        // Adicionar classe ao input
        this.searchInput.classList.add('search-input-with-clear');
        
        // Armazenar referências
        this.clearBtn = clearBtn;
        this.container = container;
    }

    setupEventListeners() {
        // Adicionar evento de input para pesquisa dinâmica
        this.searchInput.addEventListener('input', (e) => {
            this.handleSearch(e.target.value);
            this.toggleClearButton();
        });
        
        // Prevenir submit do formulário tradicional
        this.searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSearch(this.searchInput.value);
        });
        
        // Evento do botão de limpar
        this.clearBtn.addEventListener('click', () => {
            this.clearSearch();
        });
    }

    toggleClearButton() {
        if (this.searchInput.value.length > 0) {
            this.clearBtn.style.display = 'flex';
        } else {
            this.clearBtn.style.display = 'none';
        }
    }

    clearSearch() {
        this.searchInput.value = '';
        this.toggleClearButton();
        
        // Recarregar a página original para restaurar paginação
        const url = new URL(window.location);
        url.searchParams.delete('search');
        window.location.href = url.toString();
    }
    
    handleSearch(query) {
        // Cancelar timer anterior
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        
        // Cancelar requisição anterior se ainda estiver pendente
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
        
        // Debounce da pesquisa
        this.debounceTimer = setTimeout(() => {
            this.performSearch(query);
        }, this.debounceDelay);
    }
    
    performSearch(query) {
        // Se a query for muito curta, limpar resultados ou mostrar todos
        if (query.length < this.minLength && query.length > 0) {
            return;
        }
        
        // Preparar dados para a requisição
        const formData = new FormData(this.searchForm);
        formData.set('search', query);
        formData.set('ajax', '1'); // Indicador para o controller
        
        // Construir URL com parâmetros
        const url = new URL(this.searchForm.action);
        const params = new URLSearchParams(formData);
        url.search = params.toString();
        
        // Obter token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Fazer requisição AJAX
        this.currentRequest = fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            this.updateResults(html);
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                console.error('Erro na pesquisa:', error);
                this.showError('Erro ao realizar pesquisa. Tente novamente.');
            }
        })
        .finally(() => {
            this.currentRequest = null;
        });
    }
    
    updateResults(html) {
        // Criar um elemento temporário para parsear o HTML
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        
        // Encontrar especificamente a tabela no HTML retornado
        const newTable = tempDiv.querySelector('.table-responsive');
        const newPagination = tempDiv.querySelector('.d-flex.justify-content-between');
        const noResults = tempDiv.querySelector('.text-center.py-5') || tempDiv.querySelector('.alert-info');
        
        if (this.resultsContainer) {
            // Encontrar a tabela atual e paginação
            const currentTable = this.resultsContainer.querySelector('.table-responsive');
            const currentPagination = this.resultsContainer.querySelector('.d-flex.justify-content-between');
            const currentNoResults = this.resultsContainer.querySelector('.text-center.py-5') || this.resultsContainer.querySelector('.alert-info');
            
            // Remover elementos existentes
            if (currentTable) currentTable.remove();
            if (currentPagination) currentPagination.remove();
            if (currentNoResults) currentNoResults.remove();
            
            // Adicionar novos elementos
            if (newTable) {
                this.resultsContainer.appendChild(newTable);
            }
            if (newPagination) {
                this.resultsContainer.appendChild(newPagination);
                // Interceptar cliques nos links de paginação
                this.setupPaginationListeners(newPagination);
            }
            if (noResults) {
                this.resultsContainer.appendChild(noResults);
            }
            
            // Atualizar URL sem recarregar a página
            const url = new URL(window.location);
            if (this.searchInput.value) {
                url.searchParams.set('search', this.searchInput.value);
            } else {
                url.searchParams.delete('search');
            }
            window.history.replaceState({}, '', url);
        }
    }
    
    setupPaginationListeners(paginationContainer) {
        // Encontrar todos os links de paginação
        const paginationLinks = paginationContainer.querySelectorAll('a.page-link');
        
        paginationLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                const url = new URL(link.href);
                const page = url.searchParams.get('page');
                
                if (page) {
                    this.loadPage(page);
                }
            });
        });
    }
    
    loadPage(page) {
        // Preparar dados para a requisição incluindo a página
        const formData = new FormData(this.searchForm);
        formData.set('search', this.searchInput.value);
        formData.set('page', page);
        formData.set('ajax', '1');
        
        // Construir URL com parâmetros
        const url = new URL(this.searchForm.action);
        const params = new URLSearchParams(formData);
        url.search = params.toString();
        
        // Obter token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Fazer requisição AJAX
        this.currentRequest = fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(html => {
            this.updateResults(html);
            
            // Atualizar URL com a página atual
            const currentUrl = new URL(window.location);
            if (this.searchInput.value) {
                currentUrl.searchParams.set('search', this.searchInput.value);
            }
            currentUrl.searchParams.set('page', page);
            window.history.replaceState({}, '', currentUrl);
        })
        .catch(error => {
            if (error.name !== 'AbortError') {
                console.error('Erro ao carregar página:', error);
                this.showError('Erro ao carregar página. Tente novamente.');
            }
        })
        .finally(() => {
            this.currentRequest = null;
        });
    }

    
    showError(message) {
        // Remover erro anterior se existir
        const existingError = document.getElementById('search-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Criar novo elemento de erro
        const errorDiv = document.createElement('div');
        errorDiv.id = 'search-error';
        errorDiv.className = 'alert alert-danger mt-3';
        errorDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Inserir após o formulário de pesquisa
        if (this.searchForm.parentNode) {
            this.searchForm.parentNode.insertBefore(errorDiv, this.searchForm.nextSibling);
        }
        
        // Auto-remover após 5 segundos
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
}

// CSS para o botão de limpar pesquisa
const style = document.createElement('style');
style.textContent = `
    .search-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }
    .search-clear-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        font-size: 16px;
        color: #6c757d;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }
    .search-clear-btn:hover {
        color: #dc3545;
    }
    .search-input-with-clear {
        padding-right: 35px !important;
    }
`;
document.head.appendChild(style);

// Inicializar automaticamente quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se estamos em uma página com formulário de pesquisa
    const searchInput = document.getElementById('search');
    if (searchInput) {
        new LiveSearch();
    }
});

// Exportar para uso global
window.LiveSearch = LiveSearch;