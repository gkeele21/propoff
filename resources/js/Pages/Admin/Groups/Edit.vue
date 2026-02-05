<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Base/Button.vue';
import Card from '@/Components/Base/Card.vue';
import Icon from '@/Components/Base/Icon.vue';
import TextField from '@/Components/Form/TextField.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    group: Object,
});

const form = useForm({
    name: props.group.name,
    code: props.group.code,
});

const submit = () => {
    form.patch(route('admin.groups.update', props.group.id));
};
</script>

<template>
    <Head title="Edit Group" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Edit Group"
                :crumbs="[
                    { label: 'Groups', href: route('admin.groups.index') },
                    { label: group.name, href: route('admin.groups.show', group.id) },
                    { label: 'Edit' }
                ]"
            >
                <template #metadata>
                    <span class="font-medium text-body">{{ group.name }}</span>
                </template>
            </PageHeader>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name -->
                        <TextField
                            v-model="form.name"
                            label="Group Name"
                            :error="form.errors.name"
                            hint="The display name for this group"
                            required
                            autofocus
                        />

                        <!-- Code -->
                        <TextField
                            v-model="form.code"
                            label="Group Code"
                            :error="form.errors.code"
                            hint="Members use this code to join the group. Only uppercase letters, numbers, and hyphens allowed."
                            class="uppercase"
                            required
                            maxlength="50"
                            pattern="[A-Z0-9-]+"
                        />

                        <!-- Warning Box -->
                        <div class="bg-warning/10 border border-warning/30 rounded-lg p-4">
                            <h4 class="font-semibold text-warning mb-2 flex items-center gap-2">
                                <Icon name="triangle-exclamation" size="sm" />
                                Important
                            </h4>
                            <ul class="text-sm text-warning/80 space-y-1 ml-6 list-disc">
                                <li>Changing the group code will invalidate the old code</li>
                                <li>Members will need the new code to join</li>
                                <li>Existing members will not be affected</li>
                            </ul>
                        </div>

                        <!-- Group Info -->
                        <div class="bg-surface-inset rounded-lg p-4">
                            <h4 class="text-sm font-medium text-body mb-2">Group Information</h4>
                            <dl class="text-sm text-muted space-y-1">
                                <div class="flex justify-between">
                                    <dt>Created:</dt>
                                    <dd class="text-body">{{ new Date(group.created_at).toLocaleDateString() }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t border-border">
                            <Link
                                :href="route('admin.groups.show', group.id)"
                                class="text-muted hover:text-body transition-colors"
                            >
                                Cancel
                            </Link>
                            <Button
                                variant="primary"
                                type="submit"
                                :loading="form.processing"
                            >
                                <Icon name="check" class="mr-2" size="sm" />
                                Update Group
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
