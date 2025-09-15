import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
});

export interface User {
    id: number;
    name: string;
    email: string;
}

export interface Comment {
    id: number;
    content: string;
    rating: number;
    created_at: string;
    post_id: number;
    user: User;
}

export interface Post {
    id: number;
    title: string;
    content: string;
    created_at: string;
    user: User;
    comments?: Comment[];
}

export interface PaginatedResponse<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: Array<{ url: string | null; label: string; page: number | null; active: boolean }>;
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}

export const PostsAPI = {
    list: (page: number = 1): Promise<PaginatedResponse<Post>> =>
        api.get(`/posts?page=${page}`).then(res => res.data),

    create: (data: { title: string; content: string }) =>
        api.post('/posts', data).then(res => res.data.data),

    update: (id: number, data: { title: string; content: string }) =>
        api.put(`/posts/${id}`, data).then(res => res.data.data),

    delete: (id: number) =>
        api.delete(`/posts/${id}`),
};

export const CommentsAPI = {
    list: (postId: number) =>
        api.get<{ data: Comment[] }>(`/posts/${postId}/comments`),

    create: (postId: number, data: { content: string }) =>
        api.post<{ data: Comment }>(`/posts/${postId}/comments`, data),

    update: (postId: number, commentId: number, data: { content: string }) =>
        api.put<{ data: Comment }>(`/posts/${postId}/comments/${commentId}`, data),

    delete: (postId: number, commentId: number) =>
        api.delete(`/posts/${postId}/comments/${commentId}`),

    vote: (postId: number, commentId: number, direction: 'up' | 'down') =>
        api.post<{ rating: number }>(`/posts/${postId}/comments/${commentId}/vote/${direction}`),
};
