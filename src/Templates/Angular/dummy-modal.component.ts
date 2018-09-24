import { Component, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { Dummy } from './dummy';
import { DummyService } from './dummys.service';

@Component({
    selector: 'app-dummy-modal',
    templateUrl: './dummy-modal.component.html'
})
export class DummyModalComponent {
    dummy: Dummy;
    constructor(public activeModal: NgbActiveModal, private dummyService: DummyService) {}
    /*this.activeModal.close(): void{
        alert('closing');
    }*/
    saveDummy(): void {
        if (this.dummy.id) {
            this.dummyService.updateDummy(this.dummy.id, this.dummy)
                .subscribe(
                    dummy => this.activeModal.close({is_new: false, dummy: dummy}),
                    error => console.log(error)
                );
        } else {
            this.dummyService.createDummy(this.dummy)
                .subscribe(
                    dummy => this.activeModal.close({is_new: true, dummy: dummy}),
                    error => console.log(error)
                );
        }

    }
}
