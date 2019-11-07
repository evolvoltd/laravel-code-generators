import { mount, createLocalVue } from '@vue/test-utils';
import DummyTable from '../../src/components/DummyTable';
import { mockDummys } from './mock/dummykcs';

describe('DummyTable', () => {
  let wrapper;
  const vuetify = new Vuetify();
  const localVue = createLocalVue();

  beforeEach(() => {
    wrapper = mount(DummyTable, {
      localVue,
      vuetify,
      propsData: {
        rows: mockDummys,
        pagination: {
          totalItems: 100,
          rowsPerPage: 50,
          page: 1,
        },
      },
    });
  });

  it('should render a row for each item in props.rows + a header row', () => {
    expect(wrapper.findAll('tr')).toHaveLength(mockUsers.length + 1);
  });

  // it('should emit a change-page event with page number as payload when next page button is clicked', () => {
  //   wrapper.find('button[aria-label="Next page"]').trigger('click');
  //   expect(wrapper.emitted()['change-page'][0]).toEqual([2]);
  // });

  // it('should emit an edit event with dummy as payload when edit button is clicked', () => {
  //   wrapper.findAll('.v-btn--icon').at(0).trigger('click');
  //   expect(wrapper.emitted().edit[0]).toEqual([wrapper.vm.displayedItems[0]]);
  // });
  //
  // it('should emit a custom event with dummy as payload when impersonate button is clicked', () => {
  //   wrapper.find('.js-actions-menu-activator').trigger('click');
  //   wrapper.findAll('.v-menu__content .v-list-item').at(0).trigger('click');
  //   expect(wrapper.emitted().impersonate[0]).toEqual([wrapper.vm.displayedItems[0]]);
  // });

  it('matches snapshot', () => {
    expect(wrapper.html()).toMatchSnapshot();
  });
});
