import { Component, OnInit } from '@angular/core';

import { Dummy } from './dummy';
import { DummyPaginated } from './dummy-paginated';
import { DummyService } from './dummys.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { DummyModalComponent } from './dummy-modal.component';

@Component({
  selector: 'app-dummys-list',
  templateUrl: './dummys.component.html',
  providers: [DummyService]

})
export class DummysComponent implements OnInit {

  dummys: DummyPaginated;
  selectedDummy: Dummy;

  constructor(private dummyService: DummyService, private modalService: NgbModal) { }

  getDummys(): void {
    this.dummyService.getDummys().subscribe(dummys => this.dummys = dummys);
  }
  ngOnInit(): void {
    this.getDummys();
  }
  editDummy(dummy: Dummy): void {
    this.selectedDummy = dummy;
    const clonedDummy = { ...this.selectedDummy }
    const modalRef = this.modalService.open(DummyModalComponent);
      modalRef.result.then(result => {
          if (result.is_new) {
              this.dummys.data.push(result.dummy);
          } else {
              const index = this.dummys.data.indexOf(this.selectedDummy);
              if (index >= 0) {
                  this.dummys.data[index] = result.dummy;
              }
          }
        this.selectedDummy = null;
    }, reason => console.log(reason));
    modalRef.componentInstance.dummy = clonedDummy;
  }

}
