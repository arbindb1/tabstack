import { Head } from '@inertiajs/react';
import ConnectionForm from '@/components/connection-form';
import Heading from '@/components/heading';
import { index, update } from '@/routes/connections';

type Connection = {
    id: number;
    name: string;
    driver: 'mysql' | 'pgsql' | 'sqlite';
    host: string | null;
    port: string | null;
    database: string;
    username: string | null;
};

export default function EditConnection({
    connection,
}: {
    connection: Connection;
}) {
    return (
        <>
            <Head title="Edit connection" />

            <div className="max-w-xl space-y-6 p-4">
                <Heading
                    title="Edit connection"
                    description="Update this database connection"
                />

                <ConnectionForm
                    connection={{
                        name: connection.name,
                        driver: connection.driver,
                        host: connection.host ?? '',
                        port: connection.port ?? '',
                        database: connection.database,
                        username: connection.username ?? '',
                        password: '',
                    }}
                    submitUrl={update.url(connection.id)}
                    method="put"
                />
            </div>
        </>
    );
}

EditConnection.layout = {
    breadcrumbs: [
        { title: 'Connections', href: index() },
        { title: 'Edit', href: '' },
    ],
};
