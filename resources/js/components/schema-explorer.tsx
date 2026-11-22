import { ChevronRight } from 'lucide-react';
import { useEffect, useState } from 'react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { cn } from '@/lib/utils';

type Column = {
    name: string;
    type: string;
    nullable: boolean;
};

type Table = {
    name: string;
    columns: Column[];
};

export default function SchemaExplorer({ schemaUrl }: { schemaUrl: string }) {
    const [tables, setTables] = useState<Table[] | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let cancelled = false;

        setTables(null);
        setError(null);

        fetch(schemaUrl, { headers: { Accept: 'application/json' } })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Failed to load schema');
                }

                return response.json();
            })
            .then((body: { tables: Table[] }) => {
                if (!cancelled) {
                    setTables(body.tables);
                }
            })
            .catch(() => {
                if (!cancelled) {
                    setError('Could not load schema.');
                }
            });

        return () => {
            cancelled = true;
        };
    }, [schemaUrl]);

    if (error) {
        return (
            <p className="p-2 text-sm text-red-600 dark:text-red-400">
                {error}
            </p>
        );
    }

    if (tables === null) {
        return (
            <p className="p-2 text-sm text-muted-foreground">Loading schema…</p>
        );
    }

    if (tables.length === 0) {
        return (
            <p className="p-2 text-sm text-muted-foreground">
                No tables found.
            </p>
        );
    }

    return (
        <div className="space-y-1">
            {tables.map((table) => (
                <TableNode key={table.name} table={table} />
            ))}
        </div>
    );
}

function TableNode({ table }: { table: Table }) {
    const [open, setOpen] = useState(false);

    return (
        <Collapsible open={open} onOpenChange={setOpen}>
            <CollapsibleTrigger className="flex w-full items-center gap-1 rounded-md px-2 py-1 text-left text-sm font-medium hover:bg-accent">
                <ChevronRight
                    className={cn(
                        'size-4 shrink-0 transition-transform',
                        open && 'rotate-90',
                    )}
                />
                {table.name}
            </CollapsibleTrigger>
            <CollapsibleContent className="ml-5 space-y-0.5 border-l pl-2">
                {table.columns.map((column) => (
                    <div
                        key={column.name}
                        className="flex items-center justify-between gap-2 px-2 py-0.5 text-sm text-muted-foreground"
                    >
                        <span>{column.name}</span>
                        <span className="text-xs">
                            {column.type}
                            {column.nullable ? '?' : ''}
                        </span>
                    </div>
                ))}
            </CollapsibleContent>
        </Collapsible>
    );
}
