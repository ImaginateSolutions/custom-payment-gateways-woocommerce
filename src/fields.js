const { TextInput, SortSelect, Textarea, CheckboxControl, ValidatedTextInput } = window.wc.blocksComponents;

const Fields = (props) => {
    const { attributes, event } = props;
    const { type, inclass } = attributes;
    
    switch(type) {
        case 'text':
        case 'number':
        case 'password':
        case 'tel':
            return  <TextInput
                {...attributes}
                onChange={ event }
                className={`wc-block-components-text-input ${inclass}`}
            />
        case 'url':
        case 'email':
            return <ValidatedTextInput
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
                </div>
        default:
            return null
    }
}

export default Fields;