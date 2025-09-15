import React, {useEffect, useState} from 'react';
import {Comment, CommentsAPI, Post, PostsAPI} from '@/lib/api';
import {Card} from '@/components/ui/card';
import {Button} from '@/components/ui/button';
import {Skeleton} from '@/components/ui/skeleton';
import {Textarea} from '@/components/ui/textarea';

interface AppPostDetailProps {
    postId: number;
}

export default function AppPostDetail({postId}: AppPostDetailProps) {
    const [post, setPost] = useState<Post | null>(null);
    const [comments, setComments] = useState<Comment[]>([]);
    const [loadingPost, setLoadingPost] = useState<boolean>(true);
    const [loadingComments, setLoadingComments] = useState<boolean>(true);
    const [newComment, setNewComment] = useState<string>('');
    const [submittingComment, setSubmittingComment] = useState<boolean>(false);

    useEffect(() => {
        setLoadingPost(true);
        PostsAPI.list()
            .then(resp => {
                const found = resp.data.find(p => p.id === postId);
                setPost(found ?? null);
            })
            .catch(err => console.error('Failed to load post', err))
            .finally(() => setLoadingPost(false));
    }, [postId]);

    useEffect(() => {
        setLoadingComments(true);

        CommentsAPI.list(postId)
            .then(async resp => {
                const commentsArray: Comment[] = resp.data?.data ?? [];

                // Для каждого комментария подгружаем статус голоса
                const commentsWithVote = await Promise.all(
                    commentsArray.map(async c => {
                        try {
                            const voteResp = await CommentsAPI.voteStatus(c.post_id, c.id);
                            return {...c, userVoteDirection: voteResp.data.direction};
                        } catch {
                            return {...c, userVoteDirection: null};
                        }
                    })
                );

                setComments(commentsWithVote);
            })
            .catch(err => console.error('Failed to load comments', err))
            .finally(() => setLoadingComments(false));
    }, [postId]);

    async function handleAddComment() {
        if (!newComment.trim()) return;

        setSubmittingComment(true);
        try {
            const response = await CommentsAPI.create(postId, {content: newComment});
            const comment: Comment = response.data.data; // достаем Comment

            if (comment) {
                setComments(prev => [...prev, comment]);
                setNewComment('');
            } else {
                console.warn('Comment was created but no data returned');
            }
        } catch (err) {
            console.error('Failed to add comment', err);
        } finally {
            setSubmittingComment(false);
        }
    }

    async function handleVote(commentId: number, direction: 'up' | 'down') {
        try {
            const resp = await CommentsAPI.vote(postId, commentId, direction);
            setComments(prev =>
                prev.map(c =>
                    c.id === commentId ? {...c, rating: resp.data.rating} : c
                )
            );
        } catch (err) {
            console.error('Failed to vote', err);
        }
    }

    return (
        <div className="mt-6 space-y-6">
            {loadingPost ? (
                <Skeleton className="h-32 rounded-md"/>
            ) : post ? (
                <Card className="p-4 border border-gray-200 dark:border-gray-700">
                    <h2 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">{post.title}</h2>
                    <p className="text-gray-700 dark:text-gray-300 mt-2 whitespace-pre-line">{post.content}</p>
                    <div className="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        by {post.user?.name ?? '—'} • {new Date(post.created_at).toLocaleString()}
                    </div>
                </Card>
            ) : (
                <div className="text-gray-500 dark:text-gray-400">Post not found.</div>
            )}

            <div className="space-y-4">
                <h3 className="text-xl font-medium text-gray-900 dark:text-gray-100">Comments</h3>

                {loadingComments ? (
                    <>
                        {[...Array(3)].map((_, i) => (
                            <Skeleton key={i} className="h-16 rounded-md"/>
                        ))}
                    </>
                ) : comments.length === 0 ? (
                    <div className="text-gray-500 dark:text-gray-400">No comments yet.</div>
                ) : (
                    comments.map(c => (
                        <Card key={c.id}
                              className="p-4 border border-gray-200 dark:border-gray-700 rounded-md shadow-sm">
                            <div className="flex flex-col space-y-3">
                                <p className="text-sm text-gray-700 dark:text-gray-300">{c.content}</p>

                                <div className="text-xs text-gray-500 dark:text-gray-400 flex flex-wrap gap-2">
                                    <span>by {c.user.name}</span>
                                    <span>• {new Date(c.created_at).toLocaleString()}</span>
                                </div>

                                <div className="flex items-center space-x-2 mt-2">
                                    <Button
                                        size="sm"
                                        variant={c.userVoteDirection === 'up' ? 'secondary' : 'outline'}
                                        onClick={async () => {
                                            if (c.userVoteDirection) return; // уже голосовал
                                            try {
                                                const resp = await CommentsAPI.vote(c.post_id, c.id, 'up');
                                                setComments(prev =>
                                                    prev.map(comment =>
                                                        comment.id === c.id ? {
                                                            ...comment,
                                                            rating: resp.data.rating,
                                                            userVoteDirection: 'up'
                                                        } : comment
                                                    )
                                                );
                                            } catch (err) {
                                                console.error('Failed to vote up', err);
                                            }
                                        }}
                                        className={`scale-75 ${c.userVoteDirection === 'up' ? 'opacity-70 cursor-not-allowed' : ''}`}
                                        disabled={!!c.userVoteDirection}
                                    >
                                        👍
                                    </Button>

                                    <span
                                        className="text-sm text-gray-700 dark:text-gray-300 font-medium">{c.rating}</span>

                                    <Button
                                        size="sm"
                                        variant={c.userVoteDirection === 'down' ? 'secondary' : 'outline'}
                                        onClick={async () => {
                                            if (c.userVoteDirection) return; // уже голосовал
                                            try {
                                                const resp = await CommentsAPI.vote(c.post_id, c.id, 'down');
                                                setComments(prev =>
                                                    prev.map(comment =>
                                                        comment.id === c.id ? {
                                                            ...comment,
                                                            rating: resp.data.rating,
                                                            userVoteDirection: 'down'
                                                        } : comment
                                                    )
                                                );
                                            } catch (err) {
                                                console.error('Failed to vote down', err);
                                            }
                                        }}
                                        className={`scale-75 ${c.userVoteDirection === 'down' ? 'opacity-70 cursor-not-allowed' : ''}`}
                                        disabled={!!c.userVoteDirection}
                                    >
                                        👎
                                    </Button>
                                </div>


                            </div>
                        </Card>
                    ))
                )}

                <div className="mt-4 space-y-2">
                    <Textarea
                        placeholder="Add a comment..."
                        value={newComment}
                        onChange={e => setNewComment(e.target.value)}
                        disabled={submittingComment}
                    />
                    <Button onClick={handleAddComment} disabled={submittingComment || !newComment.trim()}>
                        {submittingComment ? 'Submitting...' : 'Post Comment'}
                    </Button>
                </div>
            </div>
        </div>
    );
}
