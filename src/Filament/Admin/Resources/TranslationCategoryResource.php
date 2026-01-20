<?php

namespace Xotriks\Servertools\Filament\Admin\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;
use Xotriks\Servertools\Models\ServerToolTranslationCategory;

class TranslationCategoryResource extends Resource
{
    protected static ?string $model = ServerToolTranslationCategory::class;

    protected static \BackedEnum|string|null $navigationIcon = 'tabler-language';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string
    {
        return trans('servertools::admin.server_tools_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('servertools::admin.translation_categories.plural');
    }

    public static function getLabel(): string
    {
        return trans('servertools::admin.translation_categories.label');
    }

    public static function getPluralLabel(): string
    {
        return trans('servertools::admin.translation_categories.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Section::make(trans('servertools::admin.translation_categories.form.section_basic'))
                        ->columnSpan(1)
                        ->schema([
                            TextInput::make('name')
                                ->label(trans('servertools::admin.translation_categories.form.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('slug', Str::slug($state));
                                }),

                            TextInput::make('slug')
                                ->label(trans('servertools::admin.translation_categories.form.slug'))
                                ->required()
                                ->maxLength(255),
                        ]),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('servertools::admin.translation_categories.table.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(trans('servertools::admin.translation_categories.table.slug'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(trans('servertools::admin.translation_categories.table.created'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make()
                    ->label(trans('servertools::admin.actions.edit')),
                DeleteAction::make()
                    ->label(trans('servertools::admin.actions.delete'))
                    ->after(function (ServerToolTranslationCategory $record): void {
                        \Log::debug('[ServerTools] translation category deleted', [
                            'category_id' => $record->id,
                            'name' => $record->name,
                            'slug' => $record->slug,
                        ]);
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            \Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\Pages\ListTranslationCategories::route('/'),
            'create' => \Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\Pages\CreateTranslationCategory::route('/create'),
            'edit' => \Xotriks\Servertools\Filament\Admin\Resources\TranslationCategoryResource\Pages\EditTranslationCategory::route('/{record}/edit'),
        ];
    }
}
