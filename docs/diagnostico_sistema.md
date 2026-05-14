# Diagnóstico e operação do sistema (Etapa 4)

## Requisitos

| Componente | Detalhe |
|------------|---------|
| PHP | 8.0 ou superior (recomendado 8.2+) |
| Extensões | `pdo`, `pdo_mysql`, `json`, `session`, `mbstring` |
| MySQL / MariaDB | 8.x ou compatível |
| Servidor | Apache com `mod_rewrite` opcional; URLs usam `URL_BASE` derivada do `DOCUMENT_ROOT` |
| Permissões | Pasta `assets/uploads/` gravável pelo usuário do PHP |

## Contrato das APIs

Todas as APIs JSON devem responder no formato:

```json
{
  "success": true,
  "message": "texto",
  "dados": {}
}
```

- `dados` é sempre um **objeto** (nunca `null` nas respostas geradas por `resposta_json()`).
- Em **401** (sessão ausente nas APIs protegidas), `dados.codigo` pode ser `nao_autenticado`.

## Ordem sugerida das migrações (banco)

1. `docs/schema_etapa2_alinhado.sql` — schema principal (pedidos, cardápio, financeiro base).
2. `docs/schema_etapa3_incremental.sql` — caixa (`caixa_sessoes`, coluna `caixa_sessao_id` em `movimentacoes_caixa`).
3. `docs/migracao_etapa3_legado.sql` — somente se ainda existir modelo **legado** (`pedidos.id_pedidos`, etc.). **Backup obrigatório.**

## Ferramentas de diagnóstico

| Caminho | Descrição |
|---------|-----------|
| `config/verificar_sistema.php` | Biblioteca + modo CLI: `php config/verificar_sistema.php` (exit code 0 = ok). |
| `publico/ferramentas/diagnostico.php` | Relatório HTML (requer login no painel). |

## Problemas conhecidos

- **Logout em HTML**: `apis/encerrar_sessao.php` sem `?formato=json` continua redirecionando para o login (compatível com links do menu). Para JSON: `apis/encerrar_sessao.php?formato=json`.
- **Alteração de senha legada**: `api/alterar_senha.php` ainda aceita POST multipart (compatibilidade); o fluxo novo usa `api/perfil.php?acao=alterar_senha` (JSON).
- **DELETE de produto**: exige `acao=deletar_item` na query string.
- **DELETE de adicional**: exige `acao=deletar_adicional`.

## Frontend unificado

- `assets/js/api_client.js` — `apiFetch()` com tratamento de 401, JSON inválido e falha de rede.
- Páginas configuram `window.__APP = { urlLogin: '...' }` antes de carregar o script.

## Checklist de testes manuais

- [ ] Login (`index.php`) → redireciona ao painel de pedidos.
- [ ] Pedidos: listar, criar, adicionar item, concluir, cancelar.
- [ ] Cardápio: categorias, adicionais, produtos (incluindo upload de imagem).
- [ ] Lixeira: listar excluídos e restaurar.
- [ ] Funcionários: listar, cadastrar (com foto), alternar status.
- [ ] Financeiro: resumo, despesas, movimentações, caixa, gráfico por ano.
- [ ] Perfil: alterar senha (JSON).
- [ ] Logout (link do nav).
- [ ] `php config/verificar_sistema.php` (CLI) ou página **Diagnóstico** no menu.

## Testes automáticos leves

Execute na raiz do projeto:

```bash
php tests/contrato_json_test.php
```

Valida o helper `resposta_json()` e o formato mínimo do envelope.
