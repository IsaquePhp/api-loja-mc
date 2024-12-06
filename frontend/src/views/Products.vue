<template>
  <div>
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-semibold text-gray-900">Produtos</h1>
          <div class="flex gap-2">
            <button @click="showAddModal = true" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              Adicionar Produto
            </button>
          </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div>
              <label class="form-label">Buscar</label>
              <input 
                type="text" 
                v-model="filters.search" 
                class="input-field" 
                placeholder="Nome, SKU ou código"
                @input="debouncedLoadProducts"
              />
            </div>
            <div>
              <label class="form-label">Categoria</label>
              <select v-model="filters.category" class="input-field" @change="loadProducts">
                <option value="">Todas</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
            <div>
              <label class="form-label">Status</label>
              <select v-model="filters.status" class="input-field" @change="loadProducts">
                <option value="">Todos</option>
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
              </select>
            </div>
            <div>
              <label class="form-label">Estoque</label>
              <select v-model="filters.stock" class="input-field" @change="loadProducts">
                <option value="">Todos</option>
                <option value="low">Baixo</option>
                <option value="normal">Normal</option>
                <option value="high">Alto</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Lista de Produtos -->
        <div class="card">
          <!-- Loading state -->
          <div v-if="loading" class="py-12 text-center text-gray-500">
            Carregando produtos...
          </div>

          <!-- Empty state -->
          <div v-else-if="products.length === 0" class="py-12 text-center text-gray-500">
            Nenhum produto encontrado.
          </div>

          <!-- Products table -->
          <table v-else class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produto
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  SKU
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Preço
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estoque
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in products" :key="product.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div>
                      <div class="text-sm font-medium text-gray-900">
                        {{ product.name }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ product.category }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ product.sku }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  R$ {{ product.price }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ product.stock }}</div>
                  <div class="text-sm text-gray-500">
                    Min: {{ product.min_stock }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(product.status)">
                    {{ product.status === 'active' ? 'Ativo' : 'Inativo' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                  <button 
                    @click="editProduct(product)"
                    class="text-blue-600 hover:text-blue-900 mr-4"
                  >
                    Editar
                  </button>
                  <button 
                    @click="deleteProduct(product.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Excluir
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modais e Toast -->
    <div v-if="showAddModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
      <div class="bg-white rounded-lg p-6 max-w-4xl w-full">
        <h2 class="text-xl font-semibold mb-4">
          {{ editingProduct ? 'Editar Produto' : 'Novo Produto' }}
        </h2>
        <form @submit.prevent="saveProduct" class="space-y-4">
          <!-- Informações Básicas -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Nome *</label>
              <input 
                type="text" 
                v-model="productForm.name" 
                class="input-field" 
                required
                :class="{'border-red-500': errors.name}"
              />
              <span v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</span>
            </div>
            
            <div>
              <label class="form-label">SKU *</label>
              <input 
                type="text" 
                v-model="productForm.sku" 
                class="input-field" 
                required
                :class="{'border-red-500': errors.sku}"
              />
              <span v-if="errors.sku" class="text-red-500 text-sm">{{ errors.sku }}</span>
              <span class="text-gray-500 text-sm">Apenas letras, números e hífen</span>
            </div>
          </div>

          <div>
            <label class="form-label">Descrição</label>
            <textarea 
              v-model="productForm.description" 
              class="input-field" 
              rows="2"
              :class="{'border-red-500': errors.description}"
            ></textarea>
            <span v-if="errors.description" class="text-red-500 text-sm">{{ errors.description }}</span>
          </div>

          <!-- Preços e Estoque -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="form-label">Preço de Venda *</label>
              <input 
                type="number" 
                v-model="productForm.price" 
                class="input-field" 
                required
                min="0"
                step="0.01"
                :class="{'border-red-500': errors.price}"
              />
              <span v-if="errors.price" class="text-red-500 text-sm">{{ errors.price }}</span>
            </div>

            <div>
              <label class="form-label">Preço de Custo *</label>
              <input 
                type="number" 
                v-model="productForm.cost_price" 
                class="input-field" 
                required
                min="0"
                step="0.01"
                :class="{'border-red-500': errors.cost_price}"
              />
              <span v-if="errors.cost_price" class="text-red-500 text-sm">{{ errors.cost_price }}</span>
            </div>

            <div>
              <label class="form-label">Estoque *</label>
              <input 
                type="number" 
                v-model="productForm.stock" 
                class="input-field" 
                required
                min="0"
                :class="{'border-red-500': errors.stock}"
              />
              <span v-if="errors.stock" class="text-red-500 text-sm">{{ errors.stock }}</span>
            </div>

            <div>
              <label class="form-label">Estoque Mínimo *</label>
              <input 
                type="number" 
                v-model="productForm.min_stock" 
                class="input-field" 
                required
                min="0"
                :class="{'border-red-500': errors.min_stock}"
              />
              <span v-if="errors.min_stock" class="text-red-500 text-sm">{{ errors.min_stock }}</span>
            </div>
          </div>

          <!-- Categoria e Unidade -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="form-label">Categoria *</label>
              <select 
                v-model="productForm.category" 
                class="input-field" 
                required
                :class="{'border-red-500': errors.category}"
              >
                <option value="">Selecione uma categoria</option>
                <option v-for="category in categories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
              <span v-if="errors.category" class="text-red-500 text-sm">{{ errors.category }}</span>
            </div>

            <div>
              <label class="form-label">Unidade *</label>
              <select
                v-model="productForm.unit" 
                class="input-field" 
                required
                :class="{'border-red-500': errors.unit}"
              >
                <option value="">Selecione uma unidade</option>
                <option value="un">Unidade (un)</option>
                <option value="kg">Quilograma (kg)</option>
                <option value="l">Litro (l)</option>
                <option value="m">Metro (m)</option>
                <option value="cx">Caixa (cx)</option>
              </select>
              <span v-if="errors.unit" class="text-red-500 text-sm">{{ errors.unit }}</span>
            </div>

            <div>
              <label class="form-label">Marca *</label>
              <input 
                type="text" 
                v-model="productForm.brand" 
                class="input-field" 
                required
                :class="{'border-red-500': errors.brand}"
              />
              <span v-if="errors.brand" class="text-red-500 text-sm">{{ errors.brand }}</span>
            </div>
          </div>

          <!-- Dimensões -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="form-label">Peso (kg) *</label>
              <input 
                type="number" 
                v-model="productForm.weight" 
                class="input-field" 
                required
                min="0"
                step="0.01"
                :class="{'border-red-500': errors.weight}"
              />
              <span v-if="errors.weight" class="text-red-500 text-sm">{{ errors.weight }}</span>
            </div>

            <div>
              <label class="form-label">Largura (cm) *</label>
              <input 
                type="number" 
                v-model="productForm.width" 
                class="input-field" 
                required
                min="0"
                step="0.1"
                :class="{'border-red-500': errors.width}"
              />
              <span v-if="errors.width" class="text-red-500 text-sm">{{ errors.width }}</span>
            </div>

            <div>
              <label class="form-label">Altura (cm) *</label>
              <input 
                type="number" 
                v-model="productForm.height" 
                class="input-field" 
                required
                min="0"
                step="0.1"
                :class="{'border-red-500': errors.height}"
              />
              <span v-if="errors.height" class="text-red-500 text-sm">{{ errors.height }}</span>
            </div>

            <div>
              <label class="form-label">Comprimento (cm) *</label>
              <input 
                type="number" 
                v-model="productForm.length" 
                class="input-field" 
                required
                min="0"
                step="0.1"
                :class="{'border-red-500': errors.length}"
              />
              <span v-if="errors.length" class="text-red-500 text-sm">{{ errors.length }}</span>
            </div>
          </div>

          <div class="flex justify-end space-x-4 mt-6">
            <button 
              type="button" 
              @click="closeModal" 
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
              :disabled="isSaving"
            >
              Cancelar
            </button>
            <button 
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md"
              :disabled="isSaving"
            >
              {{ isSaving ? 'Salvando...' : (editingProduct ? 'Atualizar' : 'Salvar') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Toast de Sucesso -->
    <div 
      v-if="showToast" 
      class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg"
    >
      {{ toastMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from '../plugins/axios'

// Refs
const products = ref([])
const categories = ref([])
const loading = ref(false)
const showAddModal = ref(false)
const showToast = ref(false)
const toastMessage = ref('')
const editingProduct = ref(null)
const isSaving = ref(false)
const errors = ref({})

// Form state
const productForm = ref({
  name: '',
  description: '',
  sku: '',
  price: '',
  cost_price: '',
  stock: '',
  min_stock: '',
  category: '',
  unit: '',
  status: 'active',
  brand: '',
  weight: '',
  width: '',
  height: '',
  length: ''
})

const filters = ref({
  search: '',
  category: '',
  status: '',
  stock: ''
})

// Carregar produtos quando o componente é montado
onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadCategories()
  ])
})

// Debounce para a busca
let searchTimeout
const debouncedLoadProducts = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadProducts()
  }, 300)
}

// Carregar produtos com loading state
async function loadProducts() {
  loading.value = true
  try {
    const response = await axios.get('/products', {
      params: filters.value
    })
    products.value = response.data
  } catch (error) {
    console.error('Error loading products:', error)
    showToast.value = true
    toastMessage.value = 'Erro ao carregar produtos'
  } finally {
    loading.value = false
  }
}

// Carregar categorias
async function loadCategories() {
  try {
    const response = await axios.get('/categories')
    categories.value = response.data.map(category => category.name)
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

// Validar SKU
function validateSku() {
  const skuPattern = /^[A-Za-z0-9-]+$/
  if (!skuPattern.test(productForm.value.sku)) {
    errors.value.sku = 'SKU deve conter apenas letras, números e hífen'
  } else {
    delete errors.value.sku
  }
}

// Salvar produto
async function saveProduct() {
  try {
    isSaving.value = true
    errors.value = {} // Limpar erros anteriores
    
    // Validar campos obrigatórios
    const requiredFields = ['name', 'sku', 'price', 'category']
    const missingFields = requiredFields.filter(field => !productForm.value[field])
    
    if (missingFields.length > 0) {
      errors.value = missingFields.reduce((acc, field) => {
        acc[field] = ['Este campo é obrigatório']
        return acc
      }, {})
      throw new Error('VALIDATION_ERROR')
    }

    // Validar estoque mínimo
    const stock = Number(productForm.value.stock) || 0
    const minStock = Number(productForm.value.min_stock) || 0
    
    if (minStock > stock) {
      errors.value.min_stock = ['O estoque mínimo não pode ser maior que o estoque atual.']
      throw new Error('VALIDATION_ERROR')
    }

    const formData = { ...productForm.value }
    console.log('Saving product:', formData) // Log para debug
    
    if (editingProduct.value) {
      const id = editingProduct.value.id
      console.log('Updating product ID:', id) // Log para debug
      await axios.put(`/products/${id}`, formData)
      toastMessage.value = 'Produto atualizado com sucesso!'
    } else {
      await axios.post('/products', formData)
      toastMessage.value = 'Produto criado com sucesso!'
    }
    
    showToast.value = true
    closeModal()
    await loadProducts() // Aguardar o recarregamento dos produtos
  } catch (error) {
    console.error('Error saving product:', error)
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else if (error.message !== 'VALIDATION_ERROR') {
      toastMessage.value = 'Erro ao salvar produto. Tente novamente.'
      showToast.value = true
    }
  } finally {
    isSaving.value = false
  }
}

function editProduct(product) {
  console.log('Editing product:', product) // Log para debug
  editingProduct.value = product
  productForm.value = { 
    name: product.name || '',
    description: product.description || '',
    sku: product.sku || '',
    price: product.price || '',
    cost_price: product.cost_price || '',
    stock: product.stock || '',
    min_stock: product.min_stock || '',
    category: product.category || '',
    unit: product.unit || '',
    status: product.status || 'active',
    brand: product.brand || '',
    weight: product.weight || '',
    width: product.width || '',
    height: product.height || '',
    length: product.length || ''
  }
  showAddModal.value = true
}

// Fechar modal
function closeModal() {
  showAddModal.value = false
  editingProduct.value = null
  productForm.value = {
    name: '',
    description: '',
    sku: '',
    price: '',
    cost_price: '',
    stock: '',
    min_stock: '',
    category: '',
    unit: '',
    status: 'active',
    brand: '',
    weight: '',
    width: '',
    height: '',
    length: ''
  }
  errors.value = {}
}

// Deletar produto
async function deleteProduct(id) {
  if (!confirm('Tem certeza que deseja excluir este produto?')) return
  
  try {
    await axios.delete(`/products/${id}`)
    showToast.value = true
    toastMessage.value = 'Produto excluído com sucesso!'
    await loadProducts()
  } catch (error) {
    console.error('Error deleting product:', error)
    showToast.value = true
    toastMessage.value = 'Erro ao excluir produto'
  }
}

// Callback quando um produto é importado
function onProductImported(product) {
  showToast.value = true
  toastMessage.value = 'Produto importado com sucesso!'
  loadProducts()
  showImportModal.value = false
}

// Estilo do status
function getStatusClass(status) {
  return status === 'active'
    ? 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'
    : 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'
}
</script>
