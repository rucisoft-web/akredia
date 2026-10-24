<?php

namespace App\Filament\App\Resources\Documents;

use App\Enums\Confidentiality;
use App\Enums\DocType;
use App\Enums\DocumentStatus;
use App\Filament\App\Resources\Documents\Pages\CreateDocument;
use App\Filament\App\Resources\Documents\Pages\EditDocument;
use App\Filament\App\Resources\Documents\Pages\ListDocuments;
use App\Filament\App\Resources\Documents\Pages\ViewDocument;
use App\Filament\App\Resources\Documents\RelationManagers\LinksRelationManager;
use App\Filament\App\Resources\Documents\RelationManagers\VersionsRelationManager;
use App\Filament\Concerns\WriterPermissions;
use App\Models\Document;
use App\Models\Unit;
use App\Services\EvidenceService;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DocumentResource extends Resource
{
    use WriterPermissions;

    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Pustaka Bukti';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Dokumen bukti';

    protected static ?string $pluralModelLabel = 'Dokumen Bukti';

    protected static ?string $recordTitleAttribute = 'title';

    public static function canEdit(Model $record): bool
    {
        return static::canWrite() && ($record->status !== DocumentStatus::Valid || static::canManage());
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleTo(auth()->user(), static::tenantInstitution())->with(['ownerUnit', 'latestVersion', 'linkCheck'])->withCount('links');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Metadata')->columns(3)->columnSpanFull()->schema([
                TextInput::make('title')->label('Judul dokumen')->required()->columnSpan(2),
                Select::make('doc_type')->label('Jenis')->options(DocType::class)->required(),
                TextInput::make('number')->label('Nomor dokumen'),
                DatePicker::make('doc_date')->label('Tanggal dokumen'),
                TextInput::make('approved_by')->label('Disahkan oleh'),
                Select::make('owner_unit_id')->label('Unit pemilik')->options(fn () => Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable()
                    ->default(fn () => static::tenantMembership()?->unit_id),
                DatePicker::make('valid_until')->label('Berlaku sampai')->helperText('Kosongkan bila tidak kedaluwarsa. Bukti kedaluwarsa tidak dihitung.'),
                Select::make('confidentiality')->label('Kerahasiaan')->options(Confidentiality::class)->default('internal')->required()
                    ->disabled(fn (Get $get) => (bool) $get('is_public'))
                    ->helperText('Rahasia: hanya unit pemilik, manajer, pimpinan, dan reviewer yang melihat.'),
                Toggle::make('is_public')->label('Tampilkan di daftar bukti publik')->live()
                    ->helperText('Untuk tautan bukti di LED yang dibuka asesor tanpa login.')->columnSpanFull(),
                Textarea::make('description')->label('Uraian singkat')->columnSpanFull(),
            ]),
            Section::make('Berkas versi pertama')->columns(2)->columnSpanFull()->visibleOn('create')
                ->description('Unggah berkas atau isi tautan. Versi berikutnya ditambahkan dari halaman dokumen; setiap versi disimpan dengan sidik SHA-256.')
                ->schema([
                    FileUpload::make('upload')->label('Berkas')->disk(EvidenceService::DISK)->directory('bukti/unggah')->visibility('private')
                        ->maxSize(fn () => (int) config('akredia.upload_max_kb', 20480))
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'])
                        ->preserveFilenames(),
                    TextInput::make('url')->label('Atau tautan (URL)')->url()->maxLength(1000),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(4)->columnSpanFull()->schema([
                TextEntry::make('code')->label('Kode')->weight('bold')->copyable(),
                TextEntry::make('doc_type')->label('Jenis')->badge(),
                TextEntry::make('status')->label('Status')->badge(),
                TextEntry::make('confidentiality')->label('Kerahasiaan')->badge(),
                TextEntry::make('number')->label('Nomor')->placeholder('—'),
                TextEntry::make('doc_date')->label('Tanggal')->date('d/m/Y')->placeholder('—'),
                TextEntry::make('approved_by')->label('Disahkan oleh')->placeholder('—'),
                TextEntry::make('valid_until')->label('Berlaku sampai')->date('d/m/Y')->placeholder('Tidak kedaluwarsa')
                    ->color(fn ($record) => $record->valid_until?->isPast() ? 'danger' : ($record->valid_until && $record->valid_until->diffInDays(now(), true) < 60 ? 'warning' : null)),
                TextEntry::make('ownerUnit.name')->label('Unit pemilik')->placeholder('—'),
                TextEntry::make('uploader.name')->label('Diunggah oleh')->placeholder('—'),
                TextEntry::make('validator.name')->label('Divalidasi oleh')->placeholder('—')
                    ->suffix(fn ($record) => $record->validated_at ? ' · '.$record->validated_at->translatedFormat('d M Y H:i') : ''),
                IconEntry::make('is_public')->label('Publik')->boolean(),
                TextEntry::make('public_url')->label('Tautan publik')->state(fn ($record) => $record->publicUrl())->url(fn ($record) => $record->publicUrl(), true)->placeholder('—')->columnSpan(2),
                TextEntry::make('linkCheck.is_ok')->label('Pemeriksaan tautan')->placeholder('—')
                    ->formatStateUsing(fn ($state, $record) => $state ? 'Baik ('.$record->linkCheck->last_status.')' : 'Rusak: '.($record->linkCheck->last_error ?? $record->linkCheck->last_status))
                    ->color(fn ($state) => $state ? 'success' : 'danger')->columnSpan(2),
                TextEntry::make('rejection_reason')->label('Alasan ditolak')->color('danger')->visible(fn ($record) => $record->status === DocumentStatus::Rejected)->columnSpanFull(),
                TextEntry::make('description')->label('Uraian')->placeholder('—')->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->weight('bold')->toggleable(),
                TextColumn::make('title')->label('Dokumen')->searchable()->wrap()->description(fn ($record) => $record->number),
                TextColumn::make('doc_type')->label('Jenis')->badge()->color('gray')->toggleable(),
                TextColumn::make('ownerUnit.name')->label('Unit')->placeholder('—')->toggleable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('confidentiality')->label('Akses')->badge()->toggleable(),
                TextColumn::make('latestVersion.version')->label('Versi')->prefix('v')->placeholder('—')->alignCenter(),
                TextColumn::make('links_count')->label('Dipakai')->suffix(' butir')->alignCenter(),
                TextColumn::make('valid_until')->label('Berlaku s.d.')->date('d/m/Y')->placeholder('—')
                    ->color(fn ($record) => $record->valid_until?->isPast() ? 'danger' : ($record->valid_until?->lt(now()->addDays(60)) ? 'warning' : null)),
                IconColumn::make('linkCheck.is_ok')->label('Tautan')->boolean()->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(DocumentStatus::class)->multiple(),
                SelectFilter::make('doc_type')->label('Jenis')->options(DocType::class)->multiple(),
                SelectFilter::make('owner_unit_id')->label('Unit')->options(fn () => Unit::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
                SelectFilter::make('confidentiality')->label('Kerahasiaan')->options(Confidentiality::class),
                TernaryFilter::make('is_public')->label('Publik'),
                TernaryFilter::make('unused')->label('Belum ditautkan ke butir')
                    ->queries(true: fn ($q) => $q->doesntHave('links'), false: fn ($q) => $q->has('links')),
                TernaryFilter::make('expiring')->label('Kedaluwarsa ≤ 60 hari')
                    ->queries(true: fn ($q) => $q->whereNotNull('valid_until')->whereDate('valid_until', '<=', now()->addDays(60)), false: fn ($q) => $q),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [VersionsRelationManager::class, LinksRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view' => ViewDocument::route('/{record}'),
            'edit' => EditDocument::route('/{record}/edit'),
        ];
    }
}
