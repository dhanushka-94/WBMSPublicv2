{{--
    The Divisions and Customer Types tables share this one layout.

    Needs: $kind ('division' or 'customer-type'), $tabKey ('divisions' or 'customer-types'), $items (paginator with customers_count),
           $label ('Division'), $plural ('divisions'), $icon, $emptyHelp, $destroyRoute (route name),
           $deleteWarning (what happens to customers that use a deleted item)
    The Alpine functions (openCreate, openEdit, askDelete, filter) live on the settings page.
--}}
@php
    $canDeleteWithCustomers = auth()->user()->isSuperAdmin();
@endphp
<div x-show="tab === '{{ $tabKey }}'" x-cloak role="tabpanel" id="panel-{{ $tabKey }}" data-kind="{{ $tabKey }}">
    <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <div class="relative w-full sm:max-w-xs">
            <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
            <input type="search" x-model="q['{{ $tabKey }}']" @input="filter('{{ $tabKey }}')"
                   placeholder="Search {{ $plural }} on this page" aria-label="Search {{ $plural }}"
                   class="w-full rounded-lg border-gray-300 pl-9 text-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <button type="button" @click="openCreate('{{ $kind }}')"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400 focus-visible:ring-offset-2">
            <i class="fas fa-plus text-xs"></i>Add {{ strtolower($label) }}
        </button>
    </div>

    @if($items->count() === 0)
        <div class="px-6 py-12 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                <i class="fas {{ $icon }} text-xl"></i>
            </span>
            <h3 class="mt-3 font-semibold text-gray-900">No {{ $plural }} yet</h3>
            <p class="mt-1 text-sm text-gray-500">{{ $emptyHelp }}</p>
            <button type="button" @click="openCreate('{{ $kind }}')" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-plus text-xs"></i>Add the first {{ strtolower($label) }}
            </button>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th scope="col" class="px-5 py-3">{{ $label }}</th>
                        <th scope="col" class="hidden px-5 py-3 md:table-cell">Description</th>
                        <th scope="col" class="px-5 py-3">Customers</th>
                        <th scope="col" class="px-5 py-3">Status</th>
                        <th scope="col" class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($items as $item)
                        @php
                            $blocked = $item->customers_count > 0 && ! $canDeleteWithCustomers;
                        @endphp
                        <tr data-row data-search="{{ \Illuminate\Support\Str::lower($item->name.' '.$item->custom_id.' '.$item->description) }}" class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex min-w-[3rem] justify-center rounded-md bg-gray-100 px-2 py-1 font-mono text-xs font-semibold text-gray-700">{{ $item->custom_id }}</span>
                                    <span class="font-medium text-gray-900">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="hidden max-w-xs truncate px-5 py-3 text-gray-600 md:table-cell" title="{{ $item->description }}">{{ $item->description ?: '—' }}</td>
                            <td class="px-5 py-3 text-gray-700">{{ number_format($item->customers_count) }}</td>
                            <td class="px-5 py-3">
                                @if($item->is_active)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button"
                                            @click="openEdit('{{ $kind }}', $event.currentTarget.dataset)"
                                            data-id="{{ $item->id }}"
                                            data-name="{{ $item->name }}"
                                            data-custom-id="{{ $item->custom_id }}"
                                            data-description="{{ $item->description }}"
                                            data-is-active="{{ $item->is_active ? 'true' : 'false' }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                                            title="Edit {{ strtolower($label) }}" aria-label="Edit {{ $item->name }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route($destroyRoute, $item) }}" method="POST" data-skip-confirm="yes" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                @click="askDelete($event)"
                                                data-label="{{ strtolower($label) }}"
                                                data-name="{{ $item->name }}"
                                                data-count="{{ $item->customers_count }}"
                                                data-warning="{{ $deleteWarning }}"
                                                @disabled($blocked)
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-gray-500"
                                                title="{{ $blocked ? 'In use by '.$item->customers_count.' customer(s), so it cannot be deleted' : 'Delete '.strtolower($label) }}"
                                                aria-label="Delete {{ $item->name }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr x-show="visible['{{ $tabKey }}'] === 0" x-cloak>
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-500">
                            No {{ $plural }} on this page match "<span class="font-medium text-gray-700" x-text="q['{{ $tabKey }}']"></span>".
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    @if($items->hasPages())
        <div class="border-t border-gray-100 px-5 py-3">{{ $items->fragment($tabKey)->links() }}</div>
    @endif
</div>
