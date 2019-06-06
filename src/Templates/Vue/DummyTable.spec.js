import { mount } from '@vue/test-utils';
import DummyTable from '../../src/components/DummyTable';
import { mockDummys } from './mock/dummykcs';

describe('DummyTable', () => {
  let wrapper;

  beforeEach(() => {
    wrapper = mount(DummyTable, {
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

  it('renders a row for each item in props.rows', () => {
    expect(wrapper.findAll('.table-row')).toHaveLength(mockDummys.length);
  });

  it('emits a rowClick event with dummy as payload when a row is clicked', () => {
    wrapper.find('.table-row').trigger('click');
    expect(wrapper.emitted().rowClick[0]).toEqual([mockDummys[0]]);
  });

  it('emits a changePage event with page number as payload when next page button is clicked', () => {
    wrapper.find('button[aria-label="Next page"]').trigger('click');
    expect(wrapper.emitted().changePage[0]).toEqual([2]);
  });

  it('emits a delete event with dummy as payload when delete button is clicked', () => {
    wrapper.findAll('.row-actions button').at(0).trigger('click');
    expect(wrapper.emitted().delete[0]).toEqual([mockDummys[0]]);
  });

  it('matches snapshot', () => {
    expect(wrapper.html()).toMatchSnapshot();
  });
});
