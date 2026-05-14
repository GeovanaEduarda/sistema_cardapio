/**
 * Cliente HTTP padronizado para as APIs do sistema.
 * Contrato esperado: { success, message, dados } (dados sempre objeto).
 *
 * Configure antes de carregar este arquivo:
 *   window.__APP = { urlLogin: '/caminho/index.php' };
 */
(function (global) {
    'use strict';

    var CFG = global.__APP || {};
    var URL_LOGIN = CFG.urlLogin || 'index.php';

    function asObject(d) {
        if (d === null || d === undefined) {
            return {};
        }
        if (typeof d === 'object' && !Array.isArray(d)) {
            return d;
        }
        return { valor: d };
    }

    function normalizePayload(parsed) {
        if (!parsed || typeof parsed !== 'object') {
            return {
                success: false,
                message: 'Resposta inválida do servidor.',
                dados: {},
            };
        }
        var out = {
            success: !!parsed.success,
            message: typeof parsed.message === 'string' ? parsed.message : 'Sem mensagem.',
            dados: asObject(parsed.dados),
        };
        return out;
    }

    /**
     * @param {string} url
     * @param {RequestInit} [options]
     * @returns {Promise<object & { _status: number, _raw?: string }>}
     */
    function apiFetch(url, options) {
        options = options || {};
        var opts = Object.assign({ credentials: 'same-origin' }, options);
        var body = opts.body;

        if (body != null && typeof body === 'string') {
            opts.headers = Object.assign({}, { 'Content-Type': 'application/json' }, opts.headers || {});
        } else if (typeof FormData !== 'undefined' && body instanceof FormData) {
            delete opts.headers;
        } else if (opts.headers) {
            opts.headers = Object.assign({}, opts.headers);
        }

        return global
            .fetch(url, opts)
            .then(function (response) {
                return response.text().then(function (text) {
                    var parsed;
                    try {
                        parsed = text ? JSON.parse(text) : {};
                    } catch (e) {
                        return Object.assign(
                            {
                                success: false,
                                message: 'JSON inválido ou resposta não é JSON.',
                                dados: {},
                            },
                            { _status: response.status, _raw: text }
                        );
                    }

                    var data = normalizePayload(parsed);
                    data._status = response.status;

                    var naoAutenticado =
                        response.status === 401 ||
                        (data.dados && data.dados.codigo === 'nao_autenticado');

                    if (naoAutenticado) {
                        global.location.href = URL_LOGIN;
                    }

                    return data;
                });
            })
            .catch(function () {
                return {
                    success: false,
                    message: 'Falha de conexão. Verifique a rede ou o servidor.',
                    dados: {},
                    _status: 0,
                };
            });
    }

    global.apiFetch = apiFetch;
})(typeof window !== 'undefined' ? window : this);
