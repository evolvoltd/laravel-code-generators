import { DummysComponent } from './dummys/dummys.component';
import { DummyDetailComponent } from './dummys/dummy-detail.component';
import { DummyModalComponent } from './dummys/dummy-modal.component';
import { DummyService } from "./dummys/dummys.service";

@NgModule({
    declarations: [
        DummysComponent,
        DummyDetailComponent,
        DummyModalComponent

    ],
    entryComponents : [DummyModalComponent],
    providers: [
        DummyService,
    ]
})