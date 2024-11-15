import { SelectControl } from '@wordpress/components';
const { TextInput, SortSelect, Textarea, CheckboxControl } = window.wc.blocksComponents;

const Fields = (props) => {
    const { attributes, event } = props;
    const { type, inclass } = attributes;
    
    switch(type) {
        case 'text':
        case 'number':
        case 'email':
        case 'password':
        case 'tel':
        case 'url':
            return  <TextInput
                {...attributes}
                onChange={ event }
                className={`wc-block-components-text-input ${inclass}`}
            />
        case 'checkbox':
            return  <CheckboxControl
                {...attributes}
                onChange={ event }
                className={`wc-block-components-checkbox__input ${inclass}`}
            />
        case 'select':
            return  <SortSelect
            {...attributes}
            onChange={event}
        />
        case 'textarea':
            return <div className='wc-block-components-textarea-wrap'>
                    <Textarea
                        {...attributes}
                        onTextChange={event}
                        placeholder={attributes.label}
                        className={`wc-block-components-textarea ${inclass}`}
                    />
                    {/* <div class="wc-block-components-validation-error" role="alert"><p>Please enter a valid test input</p></div> */}
                </div>
        default:
            return null
    }
}

export default Fields;