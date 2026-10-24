<?php

namespace App\Filament\App\Resources\AuditLogs;

use App\Filament\App\Resources\AuditLogs\Pages\ManageAuditLogs;
use App\Filament\Concerns\InteractsWithTenant;
use App\Models\AuditLog;
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

class AuditLogResource extends Resource
{
    use InteractsWithTenant;

    protected static ?string $model = AuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    protected static string|UnitEnum|null $navigationGroup = 'Administrasi';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Log audit';

    protected static ?string $pluralModelLabel = 'Log Audit';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('event')->label('Peristiwa'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('user.name')->label('Pengguna')->placeholder('Sistem')->searchable(),
                TextColumn::make('event')->label('Peristiwa')->badge()->color(fn ($state) => ['dibuat' => 'success', 'diubah' => 'info', 'dihapus' => 'danger'][$state] ?? 'gray'),
                TextColumn::make('auditable_type')->label('Objek')->formatStateUsing(fn ($record) => $record->typeLabel())->description(fn ($record) => $record->label),
                TextColumn::make('changes')->label('Perubahan')->formatStateUsing(fn ($state) => collect(is_array($state) ? $state : [])->map(fn ($v, $k) => is_array($v) && array_key_exists('dari', $v) ? $k.': '.json_encode($v['dari'], JSON_UNESCAPED_UNICODE).' → '.json_encode($v['ke'], JSON_UNESCAPED_UNICODE) : $k.(is_scalar($v) ? ': '.$v : ''))->implode('; '))->wrap()->lineClamp(3),
                TextColumn::make('ip_address')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')->label('Peristiwa')->options(fn () => \App\Models\AuditLog::query()->distinct()->orderBy('event')->pluck('event', 'event')),
                SelectFilter::make('user_id')->label('Pengguna')->relationship('user', 'name')->searchable(),
                \Filament\Tables\Filters\Filter::make('created_at')->label('Tanggal')->schema([DatePicker::make('from')->label('Dari'), DatePicker::make('until')->label('Sampai')])
                    ->query(fn ($query, array $data) => $query->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function canViewAny(): bool
    {
        return static::canManage() || static::canLead();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ManageAuditLogs::route('/')];
    }
}
