<?php

namespace App\Filament\Platform\Resources\Users;

use App\Filament\Platform\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama')->required()->maxLength(255),
            TextInput::make('email')->label('Email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('password')->label('Kata sandi')->password()->revealable()->minLength(10)
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->helperText('Kosongkan saat mengubah bila tidak diganti.'),
            Toggle::make('is_active')->label('Aktif')->default(true),
            Toggle::make('is_platform_admin')->label('Administrator platform'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->weight('bold'),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('institutions')->label('Institusi & peran')->state(fn (User $r) => $r->memberships()->with('institution')->get()
                    ->map(fn ($m) => ($m->institution?->code ?: $m->institution?->name).' · '.$m->role->getLabel())->implode(', '))->wrap()->placeholder('—'),
                IconColumn::make('is_platform_admin')->label('Admin platform')->boolean(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('last_login_at')->label('Masuk terakhir')->since()->placeholder('belum pernah'),
            ])
            ->filters([TernaryFilter::make('is_platform_admin')->label('Admin platform'), TernaryFilter::make('is_active')->label('Aktif')])
            ->recordActions([
                Action::make('reset_mfa')->label('Reset MFA')->icon(Heroicon::OutlinedKey)->requiresConfirmation()
                    ->visible(fn (User $r) => filled($r->app_authentication_secret))
                    ->action(function (User $record) {
                        $record->forceFill(['app_authentication_secret' => null, 'app_authentication_recovery_codes' => null])->save();
                        Notification::make()->success()->title('MFA direset')->send();
                    }),
                EditAction::make(),
                DeleteAction::make()->hidden(fn (User $r) => $r->id === auth()->id()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageUsers::route('/')];
    }
}
