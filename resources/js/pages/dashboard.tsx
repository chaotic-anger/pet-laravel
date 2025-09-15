import AppLayout from '@/layouts/app-layout';
import {dashboard} from '@/routes';
import {type BreadcrumbItem} from '@/types';
import {Head} from '@inertiajs/react';
import PostsList from '@/components/app-posts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard - Posts',
        href: dashboard().url,
    },
];

export default function Dashboard() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard"/>
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <PostsList/>
            </div>
        </AppLayout>
    );
}
