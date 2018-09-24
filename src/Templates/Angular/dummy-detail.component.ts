import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute, ParamMap } from '@angular/router';
import { Location } from '@angular/common';

import { DummyService } from './dummys.service';

import { Dummy } from './dummy';

import 'rxjs/add/operator/switchMap';

@Component({
    selector: 'dummy-detail',
    templateUrl: './dummy-detail.component.html'
})
export class DummyDetailComponent implements OnInit {
    @Input() dummy: Dummy;

    constructor(
        private dummyService: DummyService,
        private route: ActivatedRoute,
        private location: Location
    ) {}

    ngOnInit(): void {
        this.route.paramMap
            .switchMap((params: ParamMap) => this.dummyService.getDummy(+params.get('id')))
            .subscribe(dummy => this.dummy = dummy);
    }
    goBack(): void {
        this.location.back();
    }
}
