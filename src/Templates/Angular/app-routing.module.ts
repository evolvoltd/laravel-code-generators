import { DummysComponent } from './dummys/dummys.component';
import { DummyDetailComponent } from './dummys/dummy-detail.component';

const routes: Routes = [
    { path: 'dummys',     component: DummysComponent },
    { path: 'dummy/:id',     component: DummyDetailComponent }
];