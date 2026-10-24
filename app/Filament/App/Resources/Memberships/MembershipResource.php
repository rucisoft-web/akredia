<?php

namespace App\Filament\App\Resources\Memberships;

use App\Filament\App\Resources\Memberships\Pages\ManageMemberships;
use App\Filament\Concerns\ManagerPermissions;
use App\Models\Membership;
use App\Enums\Role;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MembershipResource extends Resource
{
    use ManagerPermissions;

    protected static ?string $model = Membership::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Administrasi';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Pengguna';

    protected static ?string $pluralModelLabel = 'Pengguna & Peran';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')->label('Pengguna')->required()->searchable()
                ->getSearchResultsUsing(fn (string $search) => \App\Models\User::query()->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")->limit(20)->pluck('name', 'id'))
                ->getOptionLabelUsing(fn ($value) => \App\Models\User::find($value)?->name)
                ->createOptionForm([
                    TextInput::make('name')->label('Nama')->required(),
                    TextInput::make('email')->label('Email')->email()->required()->unique('users', 'email'),
                    TextInput::make('password')->label('Kata sandi awal')->password()->required()->minLength(10),
                ])
                ->createOptionUsing(fn (array $data) => \App\Models\User::create($data)->id)
                ->disabledOn('edit'),
            Select::make('role')->label('Peran')->options(Role::class)->required()->helperText(fn ($state) => $state ? (is_string($state) ? Role::from($state) : $state)->description() : null)->live(),
            Select::make('unit_id')->label('Unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable()->helperText('Wajib untuk PIC dan Pemilik Data: membatasi akses bukti rahasia ke unitnya.'),
            TextInput::make('position_title')->label('Jabatan'),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Nama')->weight('bold')->searchable()->description(fn ($record) => $record->user?->email),
                TextColumn::make('role')->label('Peran')->badge(),
                TextColumn::make('unit.name')->label('Unit')->placeholder('—'),
                TextColumn::make('position_title')->label('Jabatan')->placeholder('—')->toggleable(),
                IconColumn::make('mfa')->label('MFA')->state(fn ($record) => filled($record->user?->app_authentication_secret))->boolean(),
                TextColumn::make('user.last_login_at')->label('Login terakhir')->since()->placeholder('Belum pernah')->toggleable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([
                SelectFilter::make('role')->label('Peran')->options(Role::class)->multiple(),
                SelectFilter::make('unit_id')->label('Unit')->options(fn () => \App\Models\Unit::query()->orderBy('name')->pluck('name', 'id')),
                TernaryFilter::make('is_active')->label('Aktif')->default(true),
            ])
            ->recordActions([Action::make('reset_mfa')->label('Reset MFA')->icon(Heroicon::OutlinedKey)->color('warning')->requiresConfirmation()->visible(fn ($record) => filled($record->user?->app_authentication_secret))->action(fn ($record) => $record->user->forceFill(['app_authentication_secret' => null, 'app_authentication_recovery_codes' => null])->save()), EditAction::make(), DeleteAction::make()->label('Cabut akses')]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'unit']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMemberships::route('/')];
    }
}
