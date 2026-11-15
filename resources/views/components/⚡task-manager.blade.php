<?php

use App\Models\Task;
use Livewire\Component;

new class extends Component {
    public string $title = '';
    public string $description = '';
    public ?int $editingId = null;

    protected function rules()
    {
        return [
            'title' => 'required|min:3|max:255',
            'description' => 'nullable|max:1000',
        ];
    }

    // wire:submit
    public function save()
    {
        $this->validate();

        Task::updateOrCreate(
            ['id' => $this->editingId],
            ['title' => $this->title, 'description' => $this->description]
        );

        $this->resetForm();
    }

    public function edit(int $id)
    {
        $task = Task::findOrFail($id);
        $this->editingId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description ?? '';
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function delete(int $id)
    {
        Task::findOrFail($id)->delete();
    }

    public function setStatus(int $id, string $status)
    {
        if (in_array($status, ['pending', 'in_progress', 'done'])) {
            Task::findOrFail($id)->update(['status' => $status]);
        }
    }

    private function resetForm()
    {
        $this->reset(['title', 'description', 'editingId']);
        $this->resetValidation();
    }

    public function with(): array
    {
        return ['tasks' => Task::latest()->get()];
    }
};
?>

<div>
    <h2>{{ $editingId ? 'تعديل مهمة' : 'إضافة مهمة' }}</h2>

    <form wire:submit="save">
        <input type="text" wire:model="title" placeholder="عنوان المهمة">
        @error('title') <span style="color:red">{{ $message }}</span> @enderror

        <br>
        <textarea wire:model="description" placeholder="الوصف"></textarea>
        @error('description') <span style="color:red">{{ $message }}</span> @enderror

        <br>
        <button type="submit">{{ $editingId ? 'تحديث' : 'إضافة' }}</button>
        @if($editingId)
            <button type="button" wire:click="cancelEdit">إلغاء</button>
        @endif
    </form>

    <hr>

    <h2>قائمة المهام</h2>
    @forelse($tasks as $task)
        <div wire:key="task-{{ $task->id }}" style="border:1px solid #ccc; padding:10px; margin-bottom:8px">
            <strong>{{ $task->title }}</strong>
            <p>{{ $task->description }}</p>
            <span>الحالة: {{ $task->status }}</span>
            <br>

            <button wire:click="setStatus({{ $task->id }}, 'pending')">معلّقة</button>
            <button wire:click="setStatus({{ $task->id }}, 'in_progress')">قيد التنفيذ</button>
            <button wire:click="setStatus({{ $task->id }}, 'done')">منجزة</button>
            <button wire:click="edit({{ $task->id }})">تعديل</button>
            <button wire:click="delete({{ $task->id }})" wire:confirm="متأكدة من الحذف؟">حذف</button>
        </div>
    @empty
        <p>لا توجد مهام.</p>
    @endforelse
</div>
