var getApiBaseUrl = function() {
    var protocol = window.location.protocol;
    var host = window.location.host;
    var pathname = window.location.pathname;
    
    var basePath = pathname.replace(/\/frontend\/?$/, '').replace(/\/$/, '');
    
    return protocol + '//' + host + basePath + '/api';
};

const API_BASE_URL = getApiBaseUrl();
console.log('API Base URL detectada:', API_BASE_URL);

function initVueApp() {
    console.log('initVueApp chamado. Vue disponível?', typeof Vue !== 'undefined');
    
    if (typeof Vue === 'undefined') {
        console.error('Vue.js não foi carregado! Tentando novamente em 500ms...');
        setTimeout(initVueApp, 500);
        return;
    }
    
    var appElement = document.getElementById('app');
    if (appElement && appElement.__vue__) {
        console.log('Vue já foi inicializado!');
        return;
    }
    
    console.log('Vue.js encontrado! Inicializando aplicação...');
    
    try {
        var app = new Vue({
            el: '#app',
            data: {
                loading: false,
                distances: [],
                form: {
                    cepOrigem: '',
                    cepDestino: ''
                },
                validation: {
                    origem: null,
                    destino: null
                },
                lastCalculation: null,
                selectedFile: null,
                importResult: null,
                pagination: {
                    page: 1,
                    limit: 100,
                    total: 0,
                    totalPages: 1
                }
            },
            mounted: function() {
                console.log('Vue.js inicializado com sucesso!');
                console.log('API Base URL:', API_BASE_URL);
                this.loadDistances();
            },
            methods: {
                loadDistances: function() {
                    var self = this;
                    self.loading = true;
                    
                    fetch(API_BASE_URL + '/distances?page=' + self.pagination.page + '&limit=' + self.pagination.limit)
                        .then(function(response) {
                            if (!response.ok) {
                                throw new Error('HTTP error! status: ' + response.status);
                            }
                            return response.json();
                        })
                        .then(function(data) {
                            if (data.success) {
                                self.distances = data.data || [];
                                self.pagination.total = data.total || 0;
                                self.pagination.totalPages = Math.ceil((data.total || 0) / self.pagination.limit);
                            } else {
                                console.error('Erro na resposta:', data.error);
                                alert('Erro ao carregar distâncias: ' + (data.error || 'Erro desconhecido'));
                            }
                        })
                        .catch(function(error) {
                            console.error('Erro ao carregar distâncias:', error);
                            console.error('URL da API:', API_BASE_URL);
                            console.error('Tipo de erro:', error.name, error.message);
                            
                            var errorMsg = 'Não foi possível conectar à API.\n\n';
                            errorMsg += 'URL tentada: ' + API_BASE_URL + '\n\n';
                            errorMsg += 'Verifique:\n';
                            errorMsg += '1. Se o Apache/WAMP está rodando\n';
                            errorMsg += '2. Se o mod_rewrite está habilitado\n';
                            errorMsg += '3. Se o arquivo .htaccess está no lugar correto\n';
                            errorMsg += '4. Se a URL da API está correta';
                            
                            if (self.distances.length === 0) {
                                console.warn(errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                            
                            self.distances = [];
                            self.pagination.total = 0;
                            self.pagination.totalPages = 1;
                        })
                        .finally(function() {
                            self.loading = false;
                        });
                },
                validateCep: function(type) {
                    var self = this;
                    var cep = type === 'origem' ? self.form.cepOrigem : self.form.cepDestino;
                    
                    cep = cep.replace(/[^0-9]/g, '');
                    
                    if (type === 'origem') {
                        self.form.cepOrigem = cep;
                    } else {
                        self.form.cepDestino = cep;
                    }
                    
                    if (!cep || cep.length !== 8) {
                        self.validation[type] = { valid: false, error: 'CEP deve conter 8 dígitos' };
                        return;
                    }
                    
                    fetch(API_BASE_URL + '/validate-cep', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ cep: cep })
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            var address = (data.data.street || '') + ', ' + 
                                         (data.data.neighborhood || '') + ', ' + 
                                         (data.data.city || '') + ' - ' + 
                                         (data.data.state || '');
                            self.validation[type] = { valid: true, address: address };
                        } else {
                            self.validation[type] = { valid: false, error: data.error || 'CEP inválido' };
                        }
                    })
                    .catch(function(error) {
                        console.error('Erro:', error);
                        self.validation[type] = { valid: false, error: 'Erro ao validar CEP' };
                    });
                },
                calculateDistance: function() {
                    var self = this;
                    
                    var cepOrigem = self.form.cepOrigem.replace(/[^0-9]/g, '');
                    var cepDestino = self.form.cepDestino.replace(/[^0-9]/g, '');
                    
                    self.form.cepOrigem = cepOrigem;
                    self.form.cepDestino = cepDestino;
                    
                    if (cepOrigem.length !== 8 || cepDestino.length !== 8) {
                        alert('Por favor, digite CEPs válidos com 8 dígitos');
                        return;
                    }
                    
                    self.loading = true;
                    self.lastCalculation = null;
                    
                    fetch(API_BASE_URL + '/distances', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            cep_origem: cepOrigem,
                            cep_destino: cepDestino
                        })
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            self.lastCalculation = data.data;
                            self.form.cepOrigem = '';
                            self.form.cepDestino = '';
                            self.validation.origem = null;
                            self.validation.destino = null;
                            self.loadDistances();
                        } else {
                            alert('Erro ao calcular distância: ' + data.error);
                        }
                    })
                    .catch(function(error) {
                        console.error('Erro:', error);
                        alert('Erro ao calcular distância');
                    })
                    .finally(function() {
                        self.loading = false;
                    });
                },
                handleFileSelect: function(event) {
                    this.selectedFile = event.target.files[0];
                    this.importResult = null;
                },
                importCsv: function() {
                    var self = this;
                    
                    if (!self.selectedFile) {
                        alert('Por favor, selecione um arquivo');
                        return;
                    }
                    
                    self.loading = true;
                    self.importResult = null;
                    
                    var formData = new FormData();
                    formData.append('file', self.selectedFile);
                    
                    fetch(API_BASE_URL + '/import', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        
                        if (data.success) {
                            self.importResult = data;
                            self.$refs.fileInput.value = '';
                            self.selectedFile = null;
                            self.loadDistances();
                        } else {
                            alert('Erro na importação: ' + data.error);
                        }
                    })
                    .catch(function(error) {
                        console.error('Erro:', error);
                        alert('Erro ao importar arquivo');
                    })
                    .finally(function() {
                        self.loading = false;
                    });
                },
                changePage: function(page) {
                    if (page >= 1 && page <= this.pagination.totalPages) {
                        this.pagination.page = page;
                        this.loadDistances();
                    }
                },
                formatCep: function(cep) {
                    if (!cep) return '';
                    return cep.replace(/(\d{5})(\d{3})/, '$1-$2');
                },
                formatDate: function(dateString) {
                    if (!dateString) return '';
                    var date = new Date(dateString);
                    return date.toLocaleString('pt-BR');
                }
            }
        });
        
        console.log('Aplicação Vue criada com sucesso!');
    } catch (error) {
        console.error('Erro ao inicializar Vue.js:', error);
        console.error('Stack trace:', error.stack);
        alert('Erro ao inicializar a aplicação: ' + error.message);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initVueApp, 100);
    });
} else {
    setTimeout(initVueApp, 100);
}

window.addEventListener('load', function() {
    setTimeout(initVueApp, 200);
});
