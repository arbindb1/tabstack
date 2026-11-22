import { Form, Head, Link } from '@inertiajs/react';
import DatabaseConnectionController from '@/actions/App/Http/Controllers/DatabaseConnectionController';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { create, edit, index, show } from '@/routes/connections';

type Connection = {
    id: number;
    name: string;
    driver: string;
    database: string;
    created_at: string;
};

export default function ConnectionsIndex({
    connections,
}: {
    connections: Connection[];
}) {
    return (
        <>
            <Head title="Connections" />

            <div className="space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Connections"
                        description="Saved database connections"
                    />

                    <Button asChild>
                        <Link href={create()}>New connection</Link>
                    </Button>
                </div>

                {connections.length === 0 ? (
                    <p className="text-sm text-muted-foreground">
                        No connections yet.
                    </p>
                ) : (
                    <div className="divide-y rounded-md border">
                        {connections.map((connection) => (
                            <div
                                key={connection.id}
                                className="flex items-center justify-between p-4"
                            >
                                <Link
                                    href={show(connection.id)}
                                    className="hover:underline"
                                >
                                    <p className="font-medium">
                                        {connection.name}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {connection.driver} &middot;{' '}
                                        {connection.database}
                                    </p>
                                </Link>

                                <div className="flex items-center gap-2">
                                    <Button variant="outline" asChild>
                                        <Link href={edit(connection.id)}>
                                            Edit
                                        </Link>
                                    </Button>

                                    <Form
                                        {...DatabaseConnectionController.destroy.form(
                                            connection.id,
                                        )}
                                    >
                                        {({ processing }) => (
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                disabled={processing}
                                            >
                                                Delete
                                            </Button>
                                        )}
                                    </Form>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

ConnectionsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Connections',
            href: index(),
        },
    ],
};
