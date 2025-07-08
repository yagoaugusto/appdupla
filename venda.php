<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ["danger", "Você não tem permissão para acessar esta página."];
    header("Location: principal.php");
    exit;
}

$usuario_id = $_SESSION['DuplaUserId'];
$arenas_gestor = Quadras::getArenasDoGestor($usuario_id);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$produtos_ativos = [];
if ($arena_id_selecionada) {
    // Esta nova função precisará ser criada na sua classe Lojinha
    $produtos_ativos = Lojinha::getProdutosAtivosPorArena($arena_id_selecionada);
}
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<body class="bg-gray-100 min-h-screen text-gray-800" x-data="posSystem()">

  <?php require_once '_nav_superior.php' ?>
  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php' ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-7xl mx-auto w-full">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Nova Venda</h1>
        </div>

        <?php
        if (isset($_SESSION['mensagem'])) {
            [$tipo, $texto] = $_SESSION['mensagem'];
            $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
            echo "<div class='alert {$alert_class} shadow-lg mb-5'><div><span>" . htmlspecialchars($texto) . "</span></div></div>";
            unset($_SESSION['mensagem']);
        }
        ?>
        
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6">
          <form method="GET" action="venda.php">
            <div class="form-control">
              <label class="label"><span class="label-text">Selecione a Arena para a Venda</span></label>
              <select name="arena_id" class="select select-bordered" onchange="this.form.submit()">
                <option value="">Escolha uma Arena</option>
                <?php foreach ($arenas_gestor as $arena): ?>
                  <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        </div>
        
        <?php if ($arena_id_selecionada): ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2">
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200">
                    <div class="mb-4">
                        <input type="text" x-model="searchTerm" placeholder="Buscar produto por nome..." class="input input-bordered w-full">
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 max-h-[60vh] overflow-y-auto pr-2">
                        <template x-for="produto in filteredProducts" :key="produto.id">
                            <div @click="addToCart(produto)"
                                class="card bg-base-100 shadow-xl border border-gray-200 hover:border-primary cursor-pointer transition-all duration-200">
                                <figure class="px-4 pt-4"><img :src="'img/produtos/' + (produto.imagem || 'default.png')" alt="Produto" class="rounded-xl h-24 w-full object-cover" /></figure>
                                <div class="card-body items-center text-center p-4">
                                    <h2 class="card-title text-sm" x-text="produto.nome"></h2>
                                    <p class="text-xs text-gray-500" x-text="'Estoque: ' + produto.estoque_calculado"></p>
                                    <p class="font-bold text-primary" x-text="formatCurrency(produto.preco_venda)"></p>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredProducts.length === 0" class="col-span-full text-center py-10 italic text-gray-500">
                            Nenhum produto encontrado.
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <form method="POST" action="controller-lojinha/venda_controller.php">
                    <input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>">
                    <input type="hidden" name="valor_total" :value="total">
                    <input type="hidden" name="itens_venda" :value="JSON.stringify(cart)">

                    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 sticky top-20">
                        <h2 class="text-xl font-bold mb-4 border-b pb-2">Resumo da Venda</h2>
                        <div class="max-h-[35vh] overflow-y-auto pr-2 space-y-2" x-show="cart.length > 0">
                            <template x-for="item in cart" :key="item.id">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="font-semibold text-sm" x-text="item.nome"></p>
                                        <p class="text-xs text-gray-500" x-text="formatCurrency(item.preco_venda)"></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="updateQuantity(item.id, item.quantity - 1)" class="btn btn-xs btn-ghost">-</button>
                                        <span class="font-mono text-sm" x-text="item.quantity"></span>
                                        <button type="button" @click="updateQuantity(item.id, item.quantity + 1)" class="btn btn-xs btn-ghost">+</button>
                                    </div>
                                    <div class="w-20 text-right font-semibold text-sm" x-text="formatCurrency(item.quantity * item.preco_venda)"></div>
                                </div>
                            </template>
                        </div>
                        <div class="text-center py-10 italic text-gray-500" x-show="cart.length === 0">
                            Seu carrinho está vazio.
                        </div>

                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between items-center text-lg font-extrabold">
                                <span>TOTAL</span>
                                <span class="text-primary" x-text="formatCurrency(total)"></span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div class="form-control">
                                <label class="label"><span class="label-text">Forma de Pagamento</span></label>
                                <select name="forma_pagamento" class="select select-bordered" required>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="pix">PIX</option>
                                    <option value="cartao">Cartão</option>
                                    <option value="cortesia">Cortesia</option>
                                </select>
                            </div>
                            <div class="form-control">
                                <label class="label"><span class="label-text">Cliente (Opcional)</span></label>
                                <select name="usuario_id" id="cliente-select" class="select2 w-full">
                                    <option value="">Nenhum cliente</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary w-full" :disabled="cart.length === 0">
                                Finalizar Venda
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div class="text-center bg-white p-8 rounded-xl shadow-md border border-gray-200">
                <h3 class="mt-2 text-lg font-medium text-gray-900">Nenhuma arena selecionada</h3>
                <p class="mt-1 text-sm text-gray-500">Escolha uma de suas arenas no menu acima para começar a vender.</p>
            </div>
        <?php endif; ?>

      </section>
      <br><br><br>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('posSystem', () => ({
            products: <?= json_encode($produtos_ativos) ?>,
            searchTerm: '',
            cart: [],
            
            get filteredProducts() {
                if (this.searchTerm === '') {
                    return this.products;
                }
                return this.products.filter(p => 
                    p.nome.toLowerCase().includes(this.searchTerm.toLowerCase())
                );
            },

            addToCart(product) {
                const existingItem = this.cart.find(item => item.id === product.id);
                if (existingItem) {
                    if(existingItem.quantity < product.estoque_calculado) {
                       existingItem.quantity++;
                    } else {
                        // Opcional: Adicionar um feedback visual que o estoque esgotou
                        console.warn('Estoque máximo para o produto atingido.');
                    }
                } else {
                    if (product.estoque_calculado > 0) {
                        this.cart.push({ ...product, quantity: 1 });
                    } else {
                        console.warn('Produto sem estoque.');
                    }
                }
            },

            updateQuantity(productId, quantity) {
                const item = this.cart.find(i => i.id === productId);
                if (!item) return;
                
                const product = this.products.find(p => p.id === productId);

                if (quantity <= 0) {
                    this.cart = this.cart.filter(i => i.id !== productId);
                } else if (quantity > product.estoque_calculado) {
                    item.quantity = product.estoque_calculado;
                    console.warn('Estoque máximo para o produto atingido.');
                } else {
                    item.quantity = quantity;
                }
            },
            
            get total() {
                return this.cart.reduce((sum, item) => {
                    return sum + (item.preco_venda * item.quantity);
                }, 0);
            },

            formatCurrency(value) {
                return parseFloat(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }
        }));
    });

    $(document).ready(function() {
        $('#cliente-select').select2({
            placeholder: 'Busque por nome, apelido ou CPF',
            allowClear: true,
            ajax: {
                url: 'controller-usuario/ajax-jogadores.php',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // termo de busca
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function(item) {
                            return {
                                id: item.identificador,
                                text: item.nome_completo + ' (' + (item.apelido || 'N/A') + ')'
                            };
                        })
                    };
                },
                cache: true
            }
        });
    });
  </script>

</body>
</html>