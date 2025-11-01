<?php

namespace App\Http\Controllers;

use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource. (GET /products)
     */
    public function index()
    {
        $products = Products::all();

        return response()->json([
            'status' => 'success',
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage. (POST /products)
     */
    public function store(Request $request)
    {
        try {
            // 1. Validação dos dados de entrada
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
            ]);

            // 2. Cria e salva o novo produto no banco de dados
            $product = Products::create($validatedData);

            // 3. Resposta JSON de sucesso (201 Created)
            return response()->json([
                'status' => 'success',
                'message' => 'Produto criado com sucesso!',
                'product' => $product
            ], 201);
        } catch (ValidationException $e) {
            // Resposta JSON para erros de validação (422 Unprocessable Entity)
            return response()->json([
                'status' => 'error',
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Display the specified resource. (GET /products/{id})
     */
    public function show(string $id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produto não encontrado.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage. (PUT/PATCH /products/{id})
     */
    public function update(Request $request, string $id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produto não encontrado para atualização.'
            ], 404);
        }

        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'price' => 'sometimes|numeric|min:0',
            ]);

            $product->update($validatedData);

            // Retorno 200 OK com o corpo JSON para o jQuery
            return response()->json([
                'status' => 'success',
                'message' => 'Produto atualizado com sucesso!',
                'product' => $product
            ], 200); // ⬅️ Garantindo o código 200
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro de validação na atualização',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage. (DELETE /products/{id})
     */
    public function destroy(string $id)
    {
        $product = Products::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produto não encontrado para exclusão.'
            ], 404);
        }

        $product->delete();

        // 🚨 MUDANÇA AQUI: Retorna 200 OK com corpo JSON
        // Isso permite que o jQuery leia response.message sem erro.
        return response()->json([
            'status' => 'success',
            'message' => 'Produto excluído com sucesso!'
        ], 200); // ⬅️ Alterado de 204 para 200
    }

    // Métodos create e edit omitidos
    public function create()
    {
        //
    }
    public function edit(string $id)
    {
        //
    }
}
