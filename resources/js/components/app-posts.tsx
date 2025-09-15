import React, {useEffect, useState} from 'react';
import {PaginatedResponse, Post, PostsAPI} from '@/lib/api';
import {Card} from '@/components/ui/card';
import {Button} from '@/components/ui/button';
import {Skeleton} from '@/components/ui/skeleton';

export default function PostsList() {
    const [posts, setPosts] = useState<Post[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const [page, setPage] = useState<number>(1);
    const [meta, setMeta] = useState<PaginatedResponse<Post>['meta'] | null>(null);

    useEffect(() => {
        load(page);
    }, [page]);

    async function load(pageNumber = 1) {
        setLoading(true);
        try {
            const resp = await PostsAPI.list(pageNumber);
            setPosts(resp.data);
            setMeta(resp.meta);
        } catch (e) {
            console.error('Failed to load posts', e);
        } finally {
            setLoading(false);
        }
    }

    return (
        <div className="mt-6">
            <h2 className="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Latest posts</h2>

            {loading ? (
                <div className="space-y-4">
                    {[...Array(3)].map((_, i) => (
                        <Skeleton key={i} className="h-24 rounded-md"/>
                    ))}
                </div>
            ) : posts.length === 0 ? (
                <div className="text-gray-500 dark:text-gray-400">No posts yet.</div>
            ) : (
                <div className="space-y-4">
                    {posts.map((p) => (
                        <Card key={p.id} className="p-4 border border-gray-200 dark:border-gray-700">
                            <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">{p.title}</h3>
                            <p className="text-sm text-gray-700 dark:text-gray-300 mt-1 line-clamp-3">{p.content}</p>
                            <div className="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                by {p.user?.name ?? '—'} • {new Date(p.created_at).toLocaleString()}
                            </div>
                        </Card>
                    ))}
                </div>
            )}

            {meta && (
                <div className="flex justify-between items-center mt-4">
                    <Button
                        onClick={() => setPage(page - 1)}
                        disabled={page <= 1}
                        variant="secondary"
                    >
                        Prev
                    </Button>
                    <span className="text-sm text-gray-600 dark:text-gray-400">
                        Page {meta.current_page} of {meta.last_page}
                    </span>
                    <Button
                        onClick={() => setPage(page + 1)}
                        disabled={page >= meta.last_page}
                        variant="secondary"
                    >
                        Next
                    </Button>
                </div>
            )}
        </div>
    );
}
