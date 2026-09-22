<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryIndex extends Component
{
    use WithPagination;

    // Propriedades para o formulário de criação/edição
    public ?Category $editing = null;

    public string $name = '';

    public string $color = 'zinc';

    // Controle dos modais
    public bool $showModal = false;

    public bool $confirmingDeletion = false;

    public ?Category $categoryToDelete = null;

    // Propriedades para busca e paginação
    public string $search = '';

    public int $perPage = 10;

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Propriedades para seleção
    public $selected = [];

    public bool $selectAll = false;

    // Cores disponíveis para seleção
    public array $availableColors = [
        'zinc' => 'Cinza',
        'red' => 'Vermelho',
        'orange' => 'Laranja',
        'amber' => 'Âmbar',
        'yellow' => 'Amarelo',
        'lime' => 'Lima',
        'green' => 'Verde',
        'emerald' => 'Esmeralda',
        'teal' => 'Verde-azulado',
        'cyan' => 'Ciano',
        'sky' => 'Céu',
        'blue' => 'Azul',
        'indigo' => 'Índigo',
        'violet' => 'Violeta',
        'purple' => 'Roxo',
        'fuchsia' => 'Fúcsia',
        'pink' => 'Rosa',
        'rose' => 'Rosé',
    ];

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'in:'.implode(',', array_keys($this->availableColors))],
        ];

        // Generate slug from name to validate it
        $slug = Str::slug($this->name);

        // Add unique rule for name and slug, ignoring current record if editing
        $uniqueNameRule = Rule::unique('categories', 'name');
        $uniqueSlugRule = Rule::unique('categories', 'slug')
            ->where(function ($query) use ($slug) {
                $query->where('slug', $slug);
            });

        if ($this->editing) {
            $uniqueNameRule->ignore($this->editing->id);
            $uniqueSlugRule->ignore($this->editing->id);
        }

        $rules['name'][] = $uniqueNameRule;
        $rules['name'][] = $uniqueSlugRule;

        return $rules;
    }

    protected $messages = [
        'name.required' => 'O nome da categoria é obrigatório',
        'name.unique' => 'Já existe uma categoria com este nome',
        'color.required' => 'Selecione uma cor para a categoria',
    ];

    private function getCategoriesQuery()
    {
        $query = Category::withCount('challenges');
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        return $query->latest();
    }

    public function toggleSelectAll()
    {
        // Ensure $selected is always an array
        if (! is_array($this->selected)) {
            $this->selected = [];
        }

        $perPage = $this->perPage == -1 ? 100000 : $this->perPage;

        if (count($this->selected) > 0) {
            // If any are selected, deselect all
            $this->selected = [];
            $this->selectAll = false;
        } else {
            // Select all on current page
            $this->selected = $this->getCategoriesQuery()
                ->paginate($perPage)
                ->pluck('id')
                ->toArray();
            $this->selectAll = true;
        }
    }

    public function deleteSelected()
    {
        $count = count($this->selected);
        Category::whereIn('id', $this->selected)->delete();

        $message = $count === 1
            ? 'A categoria selecionada foi excluída com sucesso!'
            : "{$count} categorias foram excluídas com sucesso!";

        $this->dispatch('toast', [
            'type' => 'error',
            'message' => $message,
            'title' => 'Exclusão realizada',
        ]);

        $this->selected = [];
        $this->selectAll = false;
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(Category $category)
    {
        $this->resetErrorBag();
        $this->editing = $category;
        $this->name = $category->name;
        $this->color = $category->color;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'color' => $this->color,
        ];

        if ($this->editing) {
            $this->editing->update($data);
            $this->dispatch('toast', [
                'type' => 'info',
                'message' => 'As alterações na categoria "'.$this->name.'" foram salvas com sucesso!',
                'title' => 'Categoria atualizada',
            ]);
        } else {
            Category::create($data);
            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'A categoria "'.$this->name.'" foi criada e está disponível para uso!',
                'title' => 'Nova categoria criada',
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete(Category $category)
    {
        $this->categoryToDelete = $category;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        if ($this->categoryToDelete) {
            $categoryName = $this->categoryToDelete->name;
            $this->categoryToDelete->delete();
            $this->dispatch('toast', [
                'type' => 'error',
                'message' => 'A categoria "'.$categoryName.'" foi removida do sistema!',
                'title' => 'Categoria excluída',
            ]);
        }

        $this->confirmingDeletion = false;
        $this->categoryToDelete = null;
    }

    private function resetForm()
    {
        $this->resetErrorBag();
        $this->editing = null;
        $this->name = '';

        $this->color = 'zinc';
    }

    public function render()
    {
        if ($this->perPage == -1) {
            $categories = $this->getCategoriesQuery()->get();

            $categories = new LengthAwarePaginator(
                $categories,
                $categories->count(),
                $categories->count(),
                1,
                ['path' => request()->url()]
            );
        } else {
            $categories = $this->getCategoriesQuery()->paginate($this->perPage);
        }

        return view('livewire.categories.category-index', [
            'categories' => $categories,
        ]);
    }
}
