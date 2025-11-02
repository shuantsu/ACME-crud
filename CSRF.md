### Algo que aprendi sobre o Laravel:

Eu estava com um erro insistente no Laravel: toda vez que fazia uma requisição AJAX (POST, PUT ou DELETE) para o meu controller, aparecia o **Erro 419 – CSRF Token inválido**.

O problema era simples. Eu tinha definido minhas rotas no **`routes/web.php`** usando **`Route::resource()`**. Como o Laravel aplica o middleware de segurança CSRF em todas as rotas web que alteram dados, o sistema bloqueava as requisições porque meu JavaScript não estava enviando o token `_token`.

### Primeira solução (funcionou, mas era trabalhosa)

A primeira forma que encontrei de resolver foi manter as rotas no **`web.php`** e ajustar o JavaScript para enviar o token CSRF. Li o token de uma tag `<meta>` ou de um campo oculto e incluí nas requisições. Isso eliminou o erro, mas deixou o processo mais complicado. Como eu estava criando um CRUD dinâmico, quase uma SPA, forçar a segurança tradicional do Laravel nesse contexto não era o ideal.

### Solução ideal (o jeito certo de fazer)

Depois percebi que o mais adequado era tratar as requisições AJAX como **endpoints de API REST**, e foi aí que veio a solução definitiva.

1. **Instalar o ambiente de API:** primeiro rodei o comando **`php artisan install:api`**, que configura o suporte a APIs e geralmente ativa o Sanctum para autenticação *stateless*. Fiz isso em um novo branch, separado do código principal.
2. **Usar `apiResource`:** em vez de `Route::resource()`, passei a usar **`Route::apiResource()`**, que já cria as rotas padronizadas de API e ignora as rotas que só fazem sentido em aplicações web (`create` e `edit`).

Não precisei mover nada para o arquivo **`routes/api.php`**, porque o novo ambiente já estava preparado para lidar com requisições de API.

Assim, o Laravel deixou de aplicar o CSRF nessas rotas, já que APIs usam **tokens de autenticação** (como Bearer Tokens) em vez de cookies.

O resultado final foi um código mais limpo, sem a necessidade de lidar com `_token`, e um fluxo de trabalho mais moderno e coerente com o padrão REST.
