import { Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import SchemaExplorer from '@/components/schema-explorer';
import { index, schema } from '@/routes/connections';

type Connection = {
    id: number;
    name: string;
    driver: string;
    database: string;
};

export default function ShowConnection({
    connection,
}: {
    connection: Connection;
}) {
    return (
        <>
            <Head title={connection.name} />

            <div className="flex h-full min-h-0 flex-1">
                <aside className="w-64 shrink-0 overflow-y-auto border-r p-2">
                    <SchemaExplorer schemaUrl={schema.url(connection.id)} />
                </aside>

                <div className="flex-1 p-4">
                    <Heading
                        title={connection.name}
                        description={`${connection.driver} · ${connection.database}`}
                    />

                    <p className="mt-4 text-sm text-muted-foreground">
                        Table data view and SQL editor tabs are coming in a
                        later phase.
                    </p>
                </div>
            </div>
        </>
    );
}

ShowConnection.layout = {
    breadcrumbs: [
        { title: 'Connections', href: index() },
        { title: 'Workspace', href: '' },
    ],
};
