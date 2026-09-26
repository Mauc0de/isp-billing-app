<div class="min-h-screen bg-gray-100 p-6">
    <div class="mx-auto max-w-6xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                Daftar Pelanggan
            </h1>

            <p class="mt-1 text-gray-600">
                Data pelanggan ISP
            </p>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4 text-left">Nama</th>
                        <th class="p-4 text-left">No. HP</th>
                        <th class="p-4 text-left">Alamat</th>
                        <th class="p-4 text-left">Paket</th>
                        <th class="p-4 text-left">Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($customers as $customer)
                        <tr class="border-t">
                            <td class="p-4">
                                {{ $customer['name'] }}
                            </td>

                            <td class="p-4">
                                {{ $customer['nohp'] }}
                            </td>

                            <td class="p-4">
                                {{ $customer['alamat'] }}
                            </td>

                            <td class="p-4">
                                {{ $customer['paket'] }}
                            </td>

                            <td class="p-4">
                                {{ $customer['status'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>