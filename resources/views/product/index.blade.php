<?php
/** @var \Illuminate\Database\Eloquent\Collection $products */
$categoryList = \App\Models\Category::getActiveAsTree();

?>

<x-app-layout>
    
    <x-category-list :category-list="$categoryList" class="-ml-5 -mt-5 -mr-5 px-4"/>
   
    <div class="flex gap-2 items-center p-3 pb-0" x-data="{
            selectedSort: '{{ request()->get('sort', '-updated_at') }}',
            searchKeyword: '{{ request()->get('search') }}',
            updateUrl() {
                const params = new URLSearchParams(window.location.search)
                if (this.selectedSort && this.selectedSort !== '-updated_at') {
                    params.set('sort', this.selectedSort)
                } else {
                    params.delete('sort')
                }

                if (this.searchKeyword) {
                    params.set('search', this.searchKeyword)
                } else {
                    params.delete('search')
                }
                window.location.href = window.location.origin + window.location.pathname + '?'
                + params.toString();
            }
        }">
        <div x-data="{ showModal: false }" class="flex gap-2 items-center p-3 pb-0">
        <form action="" method="GET" class="flex-1" @submit.prevent="updateUrl">
            <x-input type="text" name="search" placeholder="Search products"
                     x-model="searchKeyword"/>
        </form>
        <button type="button" class="btn btn-primary" @click="showModal = true">
            Image Search
        </button>
        <x-input
            x-model="selectedSort"
            @change="updateUrl"
            type="select"
            name="sort"
            class="w-full focus:border-purple-600 focus:ring-purple-600 border-gray-300 rounded-full">
            <option value="price">Price (ASC)</option>
            <option value="-price">Price (DESC)</option>
            <option value="title">Title (ASC)</option>
            <option value="-title">Title (DESC)</option>
            <option value="-updated_at">Last Modified at the top</option>
            <option value="updated_at">Last Modified at the bottom</option>
        </x-input>

        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" 
         x-show="showModal" 
         x-transition 
         @click.away="showModal = false" 
         @keydown.escape.window="showModal = false"
         style="display: none;">
        <div class="modal-content bg-white p-5 rounded shadow-lg max-w-md mx-auto">
            <div class="modal-header flex justify-between items-center">
                <h5 class="text-xl font-bold">Upload Product Image</h5>
                <button @click="showModal = false" class="btn-close">✖</button>
            </div>
            <div class="modal-body mt-4">
                <form id="imageForm" method="POST" enctype="multipart/form-data" action="{{ route('uploadProductImage') }}">
                    @csrf
                    <div class="form-outline mb-4">
                        <label for="product_image" class="form-label">Product Image</label>
                        <input type="file" name="product_image" id="product_image" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex justify-end gap-2 mt-4">
                <button type="button" class="btn btn-danger" @click="showModal = false">Close</button>
                <button type="submit" class="btn btn-primary" form="imageForm">Upload Image</button>
            </div>
        </div>
        </div>
    </div>
</div>

    <?php if ( $products->count() === 0 ): ?>
    <div class="text-center text-gray-600 py-16 text-xl">
        There are no products published
    </div>
    <?php else: ?>
    <div
        class="grid gap-2 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 p-2"
    >
        @foreach($products as $product)
            <!-- Product Item -->
            <div
                x-data="productItem({{ json_encode([
                        'id' => $product->id,
                        'slug' => $product->slug,
                        'image' => $product->image ?: '/img/noimage.png',
                        'title' => $product->title,
                        'price' => $product->price,
                        'addToCartUrl' => route('cart.add', $product)
                    ]) }})"
                    class="border border-1 border-gray-200 rounded-2xl hover:border-purple-600 transition-colors bg-white max-w-xs w-full mx-auto min-h-[450px] flex flex-col"
                >
                    <a href="{{ route('product.view', $product->slug) }}"
                       class="aspect-w-3 aspect-h-2 block overflow-hidden">
                        <img
                            :src="product.image"
                            alt=""
                            class="object-cover rounded-2xl hover:scale-105 hover:rotate-1 transition-transform"
                        />
                    </a>
                    <div class="p-4 flex-grow">
                        <h3 class="text-md">
                            <a href="{{ route('product.view', $product->slug) }}">
                                {{$product->title}}
                            </a>
                        </h3>
                        <h5 class="font-bold text-sm">Rp{{ number_format($product->price, 0, ',', '.') }}</h5>
                    </div>

                    <div class="flex justify-center py-3 px-4">
                        <button class="btn-primary" @click="addToCart()">
                            Add to Cart
                        </button>
                    </div>
                </div>
                <!--/ Product Item -->
            @endforeach
        </div>
        {{$products->appends(['sort' => request('sort'), 'search' => request('search')])->links()}}
    <?php endif; ?>
</x-app-layout>
