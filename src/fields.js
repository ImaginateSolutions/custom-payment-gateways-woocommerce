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
        case 'file':
            return <div className='wc-block-components-file-wrap'>
                    <label htmlFor={attributes.id || attributes.name}>
                        {attributes.label}
                    </label>
                    <input
                        type="file"
                        onChange={event} // pass the event, not event.target.value!
                        {...attributes}
                        className={`wc-block-components-text-input ${inclass}`}
                    />
            </div>
        default:
            return null
    }
}

export default Fields;