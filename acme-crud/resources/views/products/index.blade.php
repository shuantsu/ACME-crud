<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Produtos CRUD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos adicionais para o modal */
        .modal {
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
        }
    </style>
</head>

<body class="bg-gray-100 p-8">

    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-xl">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">Catálogo de Produtos (CRUD)</h1>

        <div class="flex justify-between items-center mb-6">
            <button id="show-create-form-btn"
                class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition duration-150">
                Adicionar Novo Produto
            </button>
            <div id="status-message" class="text-sm font-medium"></div>
        </div>

        <div id="create-product-form-container" class="mb-8 p-4 border border-gray-200 rounded-lg bg-gray-50 hidden">
            <h2 class="text-xl font-semibold mb-3 text-gray-700">Novo Produto</h2>
            <form id="create-product-form" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome:</label>
                    <input type="text" id="create-name" name="name" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Preço:</label>
                    <input type="number" step="0.01" id="create-price" name="price" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex space-x-2">
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150">Salvar</button>
                    <button type="button" id="hide-create-form-btn"
                        class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500 transition duration-150">Cancelar</button>
                </div>
            </form>
        </div>

        <h2 class="text-xl font-semibold mb-3 text-gray-700">Produtos Cadastrados</h2>
        <div id="products-list-container" class="space-y-4">
            <p>Carregando produtos...</p>
        </div>
    </div>

    <div id="edit-modal" class="modal fixed inset-0 flex items-center justify-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4">Editar Produto</h2>
            <form id="edit-product-form" class="space-y-4">
                <input type="hidden" id="edit-id">
                <div>
                    <label for="edit-name" class="block text-sm font-medium text-gray-700">Nome:</label>
                    <input type="text" id="edit-name" name="name" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                <div>
                    <label for="edit-price" class="block text-sm font-medium text-gray-700">Preço:</label>
                    <input type="number" step="0.01" id="edit-price" name="price" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="close-modal-btn"
                        class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">Cancelar</button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Atualizar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script>

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        // Use jQuery para simplificar as operações DOM e requisições AJAX
        $(document).ready(function () {
            const API_BASE_URL = 'http://127.0.0.1:8000/products';
            const $productsContainer = $('#products-list-container');
            const $statusMessage = $('#status-message');
            const $createFormContainer = $('#create-product-form-container');
            const $editModal = $('#edit-modal');

            // Função de utilidade para exibir mensagens
            function showStatus(message, isSuccess = true) {
                $statusMessage.text(message)
                    .removeClass('text-red-500 text-green-500')
                    .addClass(isSuccess ? 'text-green-500' : 'text-red-500');

                // Oculta após 4 segundos
                setTimeout(() => $statusMessage.text(''), 4000);
            }

            // ## READ (Listar Produtos)
            // -------------------------------------------------------------
            function fetchProducts() {
                $productsContainer.html('<p class="text-gray-500">Carregando produtos...</p>');
                $.ajax({
                    url: API_BASE_URL,
                    method: 'GET',
                    success: function (data) {
                        if (data.status === 'success' && Array.isArray(data.products)) {
                            renderProducts(data.products);
                        } else {
                            $productsContainer.html('<p class="text-red-500">Erro ao carregar dados da API.</p>');
                        }
                    },
                    error: function (xhr) {
                        $productsContainer.html('<p class="text-red-500">Não foi possível conectar com a API. Verifique o servidor Laravel.</p>');
                        console.error('Fetch error:', xhr.responseText);
                    }
                });
            }

            function renderProducts(products) {
                if (products.length === 0) {
                    $productsContainer.html('<p class="text-gray-500">Nenhum produto cadastrado. Adicione um acima!</p>');
                    return;
                }

                const productListHtml = products.map(product => {
                    const priceFormatted = new Intl.NumberFormat('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                    }).format(product.price);

                    return `
                        <div id="product-${product.id}" class="flex justify-between items-center p-4 border border-gray-200 rounded-md hover:bg-indigo-50 transition duration-100">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">${product.name}</h3>
                                <p class="text-gray-600">Preço: ${priceFormatted}</p>
                            </div>
                            <div class="space-x-2">
                                <button data-id="${product.id}" data-name="${product.name}" data-price="${product.price}"
                                    class="edit-btn px-3 py-1 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600">
                                    Editar
                                </button>
                                <button data-id="${product.id}"
                                    class="delete-btn px-3 py-1 bg-red-500 text-white text-sm rounded-md hover:bg-red-600">
                                    Excluir
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');

                $productsContainer.html(productListHtml);
            }


            // ## CREATE (Criar Produto)
            // -------------------------------------------------------------
            $('#show-create-form-btn').on('click', function () {
                $createFormContainer.slideDown(200);
                $(this).hide();
            });

            $('#hide-create-form-btn').on('click', function () {
                $createFormContainer.slideUp(200, function () {
                    $('#show-create-form-btn').show();
                    $('#create-product-form')[0].reset(); // Limpa o formulário
                });
            });

            $('#create-product-form').on('submit', function (e) {
                e.preventDefault();

                const productData = {
                    name: $('#create-name').val(),
                    price: $('#create-price').val(),
                };

                $.ajax({
                    url: API_BASE_URL,
                    method: 'POST',
                    data: productData,
                    success: function (response) {
                        showStatus(response.message || 'Produto criado com sucesso!', true);
                        $('#create-product-form')[0].reset();
                        $createFormContainer.slideUp(200, function () {
                            $('#show-create-form-btn').show();
                        });
                        fetchProducts(); // Recarrega a lista
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON ? xhr.responseJSON.errors : {};
                        const message = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao criar produto.';
                        let errorDetails = '';

                        // Exibe erros de validação
                        for (const field in errors) {
                            errorDetails += ` ${field}: ${errors[field].join(', ')} `;
                        }
                        showStatus(message + (errorDetails ? errorDetails : ''), false);
                    }
                });
            });


            // ## UPDATE (Editar Produto - usando Modal)
            // -------------------------------------------------------------
            // Abre o modal de edição
            $productsContainer.on('click', '.edit-btn', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const price = $(this).data('price');

                $('#edit-id').val(id);
                $('#edit-name').val(name);
                $('#edit-price').val(price);

                $editModal.removeClass('hidden');
            });

            // Fecha o modal
            $('#close-modal-btn').on('click', function () {
                $editModal.addClass('hidden');
            });

            // Lógica de submissão do formulário de edição
            $('#edit-product-form').on('submit', function (e) {
                e.preventDefault();

                const id = $('#edit-id').val();
                const updateData = {
                    name: $('#edit-name').val(),
                    price: $('#edit-price').val(),
                    // O Laravel precisa do método _method, mas o jQuery lida com o PUT/PATCH automaticamente
                };

                $.ajax({
                    url: `${API_BASE_URL}/${id}`,
                    method: 'PUT', // Usa o método HTTP correto
                    data: updateData,
                    success: function (response) {
                        showStatus(response.message || 'Produto atualizado com sucesso!', true);
                        $editModal.addClass('hidden');
                        fetchProducts(); // Recarrega a lista
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao atualizar produto.';
                        showStatus(message, false);
                    }
                });
            });


            // ## DELETE (Excluir Produto)
            // -------------------------------------------------------------
            $productsContainer.on('click', '.delete-btn', function () {
                const id = $(this).data('id');

                if (confirm('Tem certeza de que deseja excluir este produto?')) {
                    $.ajax({
                        url: `${API_BASE_URL}/${id}`,
                        method: 'DELETE', // Usa o método HTTP correto
                        success: function (response) {
                            // A API retorna 204 No Content, mas pode ter um JSON no corpo
                            showStatus(response.message || 'Produto excluído com sucesso!', true);
                            fetchProducts(); // Recarrega a lista
                        },
                        error: function (xhr) {
                            const message = xhr.responseJSON ? xhr.responseJSON.message : 'Erro ao excluir produto.';
                            showStatus(message, false);
                        }
                    });
                }
            });


            // ## Inicialização
            // -------------------------------------------------------------
            // Carrega os produtos assim que a página é carregada
            fetchProducts();
        });
    </script>
</body>

</html>