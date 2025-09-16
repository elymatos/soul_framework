<x-layout.main>
    <x-slot:head>
        <x-layout::breadcrumb :sections="[['','Home']]"></x-layout::breadcrumb>
    </x-slot:head>
    <x-slot:main>
        <div class="wt-container-center h-full">
            <div id="formDiv">
                <x-form
                    id="formImpersonating"
                    title=""
                    center="true"
                    hx-post="/impersonating"
                >
                    <x-slot:fields>
                        <div style="text-align: center">
                            <img src="/images/fnbr_logo.png" />
                        </div>
                        <x-text-field
                            id="idUser"
                            label="idUser"
                            value=""
                        ></x-text-field>
                        <x-password-field
                            id="password"
                            label="Password"
                        ></x-password-field>
                    </x-slot:fields>

                    <x-slot:buttons>
                        <x-submit
                            label="Login"
                        ></x-submit>
                    </x-slot:buttons>
                </x-form>
            </div>
        </div>
    </x-slot:main>
</x-layout.main>
