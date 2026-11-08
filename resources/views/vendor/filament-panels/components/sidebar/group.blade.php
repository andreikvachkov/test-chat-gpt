@props(['active' => false, 'items' => [], 'label' => null])

{{-- This panel uses fixed sections. No collapse state is read from localStorage. --}}
<li {{ $attributes->except(['collapsible', 'icon', 'sidebar-collapsible', 'sub-navigation'])->class(['fi-sidebar-group', 'fi-active' => $active]) }}>
    @if ($label)
        <div class="fi-sidebar-group-btn">
            <span class="fi-sidebar-group-label">{{ $label }}</span>
        </div>
    @endif

    <ul class="fi-sidebar-group-items">
        @foreach ($items as $item)
            <x-filament-panels::sidebar.item
                :active="! $item->hasActiveChildItems() && $item->isActive()"
                :active-child-items="$item->hasActiveChildItems()"
                :active-icon="$item->getActiveIcon()"
                :badge="$item->getBadge()"
                :badge-color="$item->getBadgeColor($item->getBadge())"
                :badge-tooltip="$item->getBadgeTooltip($item->getBadge())"
                :child-items="$item->getChildItems()"
                :first="$loop->first"
                :grouped="filled($label)"
                :icon="$item->getIcon()"
                :label="$item->getLabel()"
                :last="$loop->last"
                :should-open-url-in-new-tab="$item->shouldOpenUrlInNewTab()"
                :url="$item->getUrl()"
                :attributes="\Filament\Support\prepare_inherited_attributes($item->getExtraAttributeBag())"
            />
        @endforeach
    </ul>
</li>
