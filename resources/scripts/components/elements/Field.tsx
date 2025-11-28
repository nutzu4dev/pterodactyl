import React, { forwardRef } from 'react';
import { Field as FormikField, FieldProps } from 'formik';
import Input, { Textarea } from '@/components/elements/Input';
import Label from '@/components/elements/Label';

interface OwnProps {
    name: string;
    light?: boolean;
    label?: string;
    description?: string;
    validate?: (value: any) => undefined | string | Promise<any>;
}

type Props = OwnProps & Omit<React.InputHTMLAttributes<HTMLInputElement>, 'name'>;

const Field = forwardRef<HTMLInputElement, Props>(
    ({ id, name, light = false, label, description, validate, ...props }, ref) => (
        <FormikField innerRef={ref} name={name} validate={validate}>
            {({ field, form: { errors, touched } }: FieldProps) => (
                <div>
                    {label && (
                        <Label htmlFor={id} isLight={light}>
                            {label}
                        </Label>
                    )}
                    <Input
                        id={id}
                        {...field}
                        {...props}
                        isLight={light}
                        hasError={!!(touched[field.name] && errors[field.name])}
                    />
                    {touched[field.name] && errors[field.name] ? (
                        <p className={'input-help error'}>
                            {(errors[field.name] as string).charAt(0).toUpperCase() +
                                (errors[field.name] as string).slice(1)}
                        </p>
                    ) : description ? (
                        <p className={'input-help'}>{description}</p>
                    ) : null}
                </div>
            )}
        </FormikField>
    )
);
Field.displayName = 'Field';

export default Field;

type TextareaProps = OwnProps & Omit<React.TextareaHTMLAttributes<HTMLTextAreaElement>, 'name'>;

export const TextareaField = forwardRef<HTMLTextAreaElement, TextareaProps>(function TextareaField(
    { id, name, light = false, label, description, validate, className, ...props },
    ref
) {
    return (
        <FormikField innerRef={ref} name={name} validate={validate}>
            {({ field, form: { errors, touched } }: FieldProps) => (
                <div className={className}>
                    {label && (
                        <Label htmlFor={id} isLight={light}>
                            {label}
                        </Label>
                    )}
                    <Textarea
                        id={id}
                        {...field}
                        {...props}
                        isLight={light}
                        hasError={!!(touched[field.name] && errors[field.name])}
                    />
                    {touched[field.name] && errors[field.name] ? (
                        <p className={'input-help error'}>
                            {(errors[field.name] as string).charAt(0).toUpperCase() +
                                (errors[field.name] as string).slice(1)}
                        </p>
                    ) : description ? (
                        <p className={'input-help'}>{description}</p>
                    ) : null}
                </div>
            )}
        </FormikField>
    );
});
TextareaField.displayName = 'TextareaField';

