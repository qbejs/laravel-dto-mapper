<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;
use LaravelDtoMapper\Contracts\MappableDTO;

/**
 * Example DTO for blog post with multiple file attachments
 */
class CreateBlogPostDTO implements MappableDTO
{
    public string $title;
    public string $content;
    public string $category;
    public array $tags;
    public ?UploadedFile $featured_image;
    public array $attachments;
    public bool $published;
    public ?string $publish_date;

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'content' => 'required|string|min:100',
            'category' => 'required|string|in:technology,lifestyle,business,health',
            'tags' => 'required|array|min:1|max:5',
            'tags.*' => 'string|max:30',
            'featured_image' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
            'attachments' => 'nullable|array|max:3',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,zip',
            'published' => 'required|boolean',
            'publish_date' => 'nullable|required_if:published,true|date|after:now',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Tytuł nie może przekraczać 200 znaków.',
            'content.min' => 'Treść musi mieć minimum 100 znaków.',
            'category.in' => 'Wybierz prawidłową kategorię.',
            'tags.min' => 'Dodaj przynajmniej jeden tag.',
            'tags.max' => 'Maksymalnie 5 tagów.',
            'featured_image.max' => 'Zdjęcie wyróżniające nie może przekraczać 5MB.',
            'attachments.max' => 'Maksymalnie 3 załączniki.',
            'attachments.*.max' => 'Każdy załącznik nie może przekraczać 10MB.',
            'publish_date.after' => 'Data publikacji musi być w przyszłości.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'tytuł',
            'content' => 'treść',
            'category' => 'kategoria',
            'tags' => 'tagi',
            'featured_image' => 'zdjęcie wyróżniające',
            'attachments' => 'załączniki',
            'published' => 'opublikowany',
            'publish_date' => 'data publikacji',
        ];
    }
}
