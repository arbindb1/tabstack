import { useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { test } from '@/routes/connections';

type ConnectionFormValues = {
    name: string;
    driver: 'mysql' | 'pgsql' | 'sqlite';
    host: string;
    port: string;
    database: string;
    username: string;
    password: string;
};

function xsrfToken(): string {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : '';
}

export default function ConnectionForm({
    connection,
    submitUrl,
    method,
}: {
    connection?: Partial<ConnectionFormValues>;
    submitUrl: string;
    method: 'post' | 'put';
}) {
    const { data, setData, errors, processing, submit } =
        useForm<ConnectionFormValues>({
            name: connection?.name ?? '',
            driver: connection?.driver ?? 'mysql',
            host: connection?.host ?? '',
            port: connection?.port ?? '',
            database: connection?.database ?? '',
            username: connection?.username ?? '',
            password: connection?.password ?? '',
        });

    const [testing, setTesting] = useState(false);
    const [testResult, setTestResult] = useState<'success' | 'failure' | null>(
        null,
    );

    const isSqlite = data.driver === 'sqlite';

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        submit(method, submitUrl);
    };

    const handleTestConnection = async () => {
        setTesting(true);
        setTestResult(null);

        try {
            const response = await fetch(test.url(), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': xsrfToken(),
                },
                body: JSON.stringify(data),
            });

            const body: { success: boolean } = await response.json();
            setTestResult(body.success ? 'success' : 'failure');
        } catch {
            setTestResult('failure');
        } finally {
            setTesting(false);
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-2">
                <Label htmlFor="name">Name</Label>
                <Input
                    id="name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    required
                />
                <InputError message={errors.name} />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="driver">Driver</Label>
                <Select
                    value={data.driver}
                    onValueChange={(value) =>
                        setData(
                            'driver',
                            value as ConnectionFormValues['driver'],
                        )
                    }
                    name="driver"
                >
                    <SelectTrigger id="driver" className="w-full">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="mysql">MySQL</SelectItem>
                        <SelectItem value="pgsql">PostgreSQL</SelectItem>
                        <SelectItem value="sqlite">SQLite</SelectItem>
                    </SelectContent>
                </Select>
                <InputError message={errors.driver} />
            </div>

            {!isSqlite && (
                <div className="grid grid-cols-2 gap-4">
                    <div className="grid gap-2">
                        <Label htmlFor="host">Host</Label>
                        <Input
                            id="host"
                            value={data.host}
                            onChange={(e) => setData('host', e.target.value)}
                        />
                        <InputError message={errors.host} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="port">Port</Label>
                        <Input
                            id="port"
                            value={data.port}
                            onChange={(e) => setData('port', e.target.value)}
                        />
                        <InputError message={errors.port} />
                    </div>
                </div>
            )}

            <div className="grid gap-2">
                <Label htmlFor="database">
                    {isSqlite ? 'Database file path' : 'Database'}
                </Label>
                <Input
                    id="database"
                    value={data.database}
                    onChange={(e) => setData('database', e.target.value)}
                    required
                />
                <InputError message={errors.database} />
            </div>

            {!isSqlite && (
                <div className="grid grid-cols-2 gap-4">
                    <div className="grid gap-2">
                        <Label htmlFor="username">Username</Label>
                        <Input
                            id="username"
                            value={data.username}
                            onChange={(e) =>
                                setData('username', e.target.value)
                            }
                        />
                        <InputError message={errors.username} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                        />
                        <InputError message={errors.password} />
                    </div>
                </div>
            )}

            <div className="flex items-center gap-4">
                <Button type="submit" disabled={processing}>
                    Save
                </Button>

                <Button
                    type="button"
                    variant="outline"
                    disabled={testing}
                    onClick={handleTestConnection}
                >
                    {testing ? 'Testing…' : 'Test connection'}
                </Button>

                {testResult === 'success' && (
                    <span className="text-sm font-medium text-green-600">
                        Connection succeeded
                    </span>
                )}

                {testResult === 'failure' && (
                    <span className="text-sm font-medium text-red-600">
                        Connection failed
                    </span>
                )}
            </div>
        </form>
    );
}
