import React from "react";
import { Input, InputProps } from "reactstrap";

import { createAuthRequest } from "@admin/utils/api/factories";

interface IStatesProps extends Omit<InputProps, 'type' | 'ref'> {
    country?: string;
    optional: boolean;
}

type TStates = Record<string, string>;

const States = React.forwardRef<Input, IStatesProps>(({ country, optional = false, ...props }, ref) => {
    const [states, setStates] = React.useState<TStates>({});

    const fetchStates = React.useCallback(async (code: string) => {
        try {
            const response = await createAuthRequest().get<ICountry>(`/countries/${code}`);

            const newStates: TStates = {};

            if (response.data.states.length > 0) {
                for (const { code, state } of response.data.states) {
                    newStates[code] = state;
                }

                if (optional)
                    newStates[''] = 'Not Specified';
            } else {
                newStates[''] = 'Not Applicable';
            }

            setStates(newStates);
        } catch (err) {
            logger.error(err);
        }
    }, []);

    React.useEffect(() => {
        if (country)
            fetchStates(country);
    }, [country, fetchStates]);

    return (
        <>
            <Input ref={ref} type='select' {...props}>
                {Object.entries(states).map(([code, state], index) => (
                    <option key={index} value={code}>
                        {state}
                    </option>
                ))}
            </Input>
        </>
    )
});

States.displayName = 'States';

export default States;
