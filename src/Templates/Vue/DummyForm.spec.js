import { mount } from '@vue/test-utils';
import DummyForm from '../../src/components/DummyForm';
import { mockDummys } from './mock/dummys';

describe('DummyForm', () => {
  let wrapper;

  it('should render passed dummy data in form fields', () => {
    wrapper = mount(DummyForm, {
      propsData: {
        dummy: mockDummys[0],
        errors: {},
        isSavingDisabled: false,
      },
    });

    expect(wrapper.find('input[name="VUE_FORM_FIELD_NAME"]').element.value).toContain(mockDummys[0].VUE_FORM_FIELD_NAME);
  });

  it('should display an error message next its related input', () => {
    const propsData = {
      dummy: mockDummys[0],
      errors: {},
      isSavingDisabled: false,
    };
    const errorMsg = 'sample error text';

    wrapper = mount(DummyForm, {
      propsData,
    });

    propsData.errors['VUE_FORM_FIELD_NAME'] = [errorMsg];
    wrapper.setProps(JSON.parse(JSON.stringify(propsData)));

    expect(wrapper.find('.v-input').text()).toContain(errorMsg);
  });

  it('should disable save button when isSavingDisabled is true', () => {
    wrapper = mount(DummyForm, {
      propsData: {
        dummy: mockDummys[0],
        errors: {},
        isSavingDisabled: true,
      },
    });

    const button = wrapper.find('.v-card__actions button[type="button"]');
    expect(button.element.getAttribute('disabled')).toBeFalsy();
  });

  it('should emit a save event with valid dummy data', () => {
    wrapper = mount(DummyForm, {
      propsData: {
        dummy: mockDummys[0],
        errors: {},
        isSavingDisabled: false,
      },
      attachToDocument: true,
    });

    wrapper.find('.v-card__actions button[type="submit"]').trigger('click');
    expect(wrapper.emitted().save[0]).toEqual([mockDummys[0]]);
    wrapper.destroy();
  });

  it('should not allow to save with invalid data', () => {
    wrapper = mount(DummyForm, {
      propsData: {
        dummy: mockDummys[1],
        errors: {},
        isSavingDisabled: false,
      },
    });

    wrapper.find('.v-card__actions button[type="submit"]').trigger('click');
    expect(wrapper.emitted().save).toBeUndefined();
  });

  it('should emit a cancel event when cancel button is clicked', () => {
    wrapper = mount(DummyForm, {
      propsData: {
        dummy: mockDummys[0],
        errors: {},
        isSavingDisabled: false,
      },
    });

    wrapper.find('.v-card__actions button[type="button"]').trigger('click');
    expect(wrapper.emitted().cancel[0]).toBeDefined();
  });

  it('should match the snapshot', () => {
    wrapper = mount(DummyForm, {
      propsData: {
        dummy: mockDummys[0],
        errors: {},
        isSavingDisabled: false,
      },
    });

    expect(wrapper.html()).toMatchSnapshot();
  });
});
