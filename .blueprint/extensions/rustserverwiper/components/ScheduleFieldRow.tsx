import { Wipe } from './api/getWipeData';
import Input from '@/components/elements/Input';
import Select from '@/components/elements/Select';
import { useFormikContext } from 'formik';
import React from 'react';
import tw from 'twin.macro';

export default ({ week }: { week: number }) => {
    const { values, isSubmitting, setFieldValue } = useFormikContext<Wipe>();

    return (
        <div css={tw`flex gap-2 items-center`}>
            <Input
                type={'checkbox'}
                checked={!!values.repeat.find((v) => v.startsWith(String(week)))}
                onChange={(e) => {
                    e.currentTarget.checked
                        ? setFieldValue('repeat', [...values.repeat, `${week} 1 12:00`])
                        : setFieldValue(
                              'repeat',
                              values.repeat.filter((v) => !v.startsWith(String(week)))
                          );
                }}
                disabled={isSubmitting || !!values.time}
            />
            <p css={tw`whitespace-nowrap`}>
                {week}
                {week === 1 ? 'st' : 'th'} week of the month
            </p>
            {values.repeat.find((v) => v.startsWith(String(week))) && (
                <>
                    <Select
                        disabled={isSubmitting || !!values.time}
                        onChange={(e) => {
                            setFieldValue(
                                'repeat',
                                values.repeat.map((v) => {
                                    if (v.startsWith(String(week))) {
                                        const parts = v.split(' ');
                                        return `${week} ${e.currentTarget.value} ${parts[2]}`;
                                    }
                                    return v;
                                })
                            );
                        }}
                    >
                        <option
                            value={1}
                            selected={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[1] === '1'}
                        >
                            Monday
                        </option>
                        <option
                            value={2}
                            selected={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[1] === '2'}
                        >
                            Tuesday
                        </option>
                        <option
                            value={3}
                            selected={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[1] === '3'}
                        >
                            Wednesday
                        </option>
                        <option
                            value={4}
                            selected={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[1] === '4'}
                        >
                            Thursday
                        </option>
                        <option
                            value={5}
                            selected={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[1] === '5'}
                        >
                            Friday
                        </option>
                        <option
                            value={6}
                            selected={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[1] === '6'}
                        >
                            Saturday
                        </option>
                        <option
                            value={7}
                            selected={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[1] === '7'}
                        >
                            Sunday
                        </option>
                    </Select>
                    <Input
                        type={'time'}
                        defaultValue={values.repeat.find((v) => v.startsWith(String(week)))!.split(' ')[2]}
                        onChange={(e) => {
                            setFieldValue(
                                'repeat',
                                values.repeat.map((v) => {
                                    if (v.startsWith(String(week))) {
                                        const parts = v.split(' ');
                                        return `${week} ${parts[1]} ${e.currentTarget.value}`;
                                    }
                                    return v;
                                })
                            );
                        }}
                        disabled={isSubmitting || !!values.time}
                    />
                </>
            )}
        </div>
    );
};