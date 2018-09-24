import { Dummy } from './dummy';

export class DummyPaginated {
    current_page: number;
    data: Dummy[];
    from: number;
    last_page: number;
    next_page_url: string;
    path: string;
    per_page: number;
    prev_page_url: string;
    to: number;
    total: number;
}
