<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Button from '@/Components/Base/Button.vue';
import Card from '@/Components/Base/Card.vue';
import Icon from '@/Components/Base/Icon.vue';
import TextField from '@/Components/Form/TextField.vue';
import Select from '@/Components/Form/Select.vue';
import Radio from '@/Components/Form/Radio.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    events: Array,
});

const form = useForm({
    name: '',
    event_id: '',
    grading_source: 'admin',
});

const submit = () => {
    form.post(route('admin.groups.store'));
};

const eventOptions = props.events.map(event => ({
    value: event.id,
    label: `${event.name} (${new Date(event.event_date).toLocaleDateString()})`,
}));
</script>

<template>
    <Head title="Create Group" />

    <AuthenticatedLayout>
        <template #header>
            <PageHeader
                title="Create New Group"
                subtitle="Set up a new group"
                :crumbs="[
                    { label: 'Groups', href: route('admin.groups.index') },
                    { label: 'Create' }
                ]"
            />
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
                            hint="Choose a descriptive name for your group (e.g., &quot;Friends&quot;, &quot;Family&quot;, &quot;Office League&quot;)"
                            required
                            autofocus
                        />

                        <!-- Event -->
                        <Select
                            v-model="form.event_id"
                            label="Event"
                            :options="eventOptions"
                            :error="form.errors.event_id"
                            placeholder="Select an event..."
                            hint="Select the event this group will participate in"
                            required
                        />

                        <!-- Grading Source -->
                        <div>
                            <label class="block text-sm font-semibold text-muted mb-3">Grading Mode</label>
                            <div class="space-y-3">
                                <Radio
                                    v-model="form.grading_source"
                                    name="grading_source"
                                    value="admin"
                                    label="Admin Grading"
                                    description="Use admin-set event answers for grading"
                                />
                                <Radio
                                    v-model="form.grading_source"
                                    name="grading_source"
                                    value="captain"
                                    label="Captain Grading"
                                    description="Captain sets correct answers for their group"
                                />
                            </div>
                            <p v-if="form.errors.grading_source" class="text-danger text-sm mt-2">
                                {{ form.errors.grading_source }}
                            </p>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-info/10 border border-info/30 rounded-lg p-4">
                            <h4 class="font-semibold text-info mb-2 flex items-center gap-2">
                                <Icon name="circle-info" size="sm" />
                                What happens next?
                            </h4>
                            <ul class="text-sm text-info/80 space-y-1 ml-6 list-disc">
                                <li>A unique group code will be automatically generated</li>
                                <li>You can add members manually or share the group code</li>
                                <li>Members can join using the group code</li>
                                <li>You can generate event invitations for this group</li>
                            </ul>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-between pt-4 border-t border-border">
                            <Link
                                :href="route('admin.groups.index')"
                                class="text-muted hover:text-body transition-colors"
                            >
                                Cancel
                            </Link>
                            <Button
                                variant="primary"
                                type="submit"
                                :loading="form.processing"
                            >
                                <Icon name="users" class="mr-2" size="sm" />
                                Create Group
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
