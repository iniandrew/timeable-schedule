<template>
    <app-layout title="Simulasi Penjadwalan">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $props.pageTitle}}
            </h2>
        </template>

        <div id="container" class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div id="content" class="p-6">
                        <form @submit.prevent="submit" enctype="multipart/form-data">
                            <div class="mb-6">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300" for="user_avatar">Upload file</label>
                                <input
                                    @input="form.file = $event.target.files[0]"
                                    class="block w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 cursor-pointer dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" aria-describedby="user_avatar_help" id="user_avatar" type="file">
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="user_avatar_help">Upload file disini</div>
                            </div>
                            <button type="submit" @click.prevent="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="container" class="py-12" v-if="data">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div id="content" class="p-6">
                        <div class="overflow-x-auto relative">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="py-3 px-6">
                                        Activity ID
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        Student Sets
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        Subject
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        Teachers
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        Room
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        SKS
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        Schedules
                                    </th>
                                    <th scope="col" class="py-3 px-6">
                                        Schedule Time
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, index) in data" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <th scope="row" class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ item.activity_id }}
                                    </th>
                                    <td class="py-4 px-6">
                                        {{ item.students_sets }}
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ item.subject }}
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ item.teachers }}
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ item.room }}
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ item.sks }}
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ item.schedules }}
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ item.schedule_time }}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </app-layout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import {useForm} from "@inertiajs/inertia-vue3";
import {computed} from "vue";

const props = defineProps({
    pageTitle: {
        type: String,
        required: true,
    },
    data: [Object],
});

const form = useForm({
    file: null,
});

const submit = () => {
    form.post(route('scheduling.store'))
}

computed(() => {
    props.data
})

</script>

<style scoped>

</style>
