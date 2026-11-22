import { Head } from '@inertiajs/react';
import ConnectionForm from '@/components/connection-form';
import Heading from '@/components/heading';
import { index, store } from '@/routes/connections';

export default function CreateConnection() {
    return (
        <>
            <Head title="New connection" />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title="New connection"
                    description="Add a database connection"
                />

                <ConnectionForm submitUrl={store.url()} method="post" />
            </div>
        </>
    );
}

CreateConnection.layout = {
    breadcrumbs: [
        { title: 'Connections', href: index() },
        { title: 'New', href: '' },
    ],
};
