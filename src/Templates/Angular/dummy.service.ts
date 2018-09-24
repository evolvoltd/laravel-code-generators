import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Dummy } from './dummy';
import { Observable} from 'rxjs/Observable';
import { environment } from '../../environments/environment';
import { DummyPaginated } from "./dummy-paginated";

@Injectable()
export class DummyService {

    constructor(private http: HttpClient) {}

    getDummys(): Observable<DummyPaginated> {
        return this.http.get<DummyPaginated>(environment.endpoint + 'dummys');
    }
    getDummy(id: number): Observable<Dummy> {
        return this.http.get<Dummy>(environment.endpoint + 'dummys/' + id);
    }
    createDummy(dummy: Dummy): Observable<Dummy> {
        return this.http.post<Dummy>(environment.endpoint + 'dummys', dummy);
    }
    updateDummy(id: number, dummy: Dummy): Observable<Dummy> {
        return this.http.put<Dummy>(environment.endpoint + 'dummys/' + id, dummy);
    }
}
