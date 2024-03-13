import React from 'react';
import { Helmet } from 'react-helmet';
import { FormikHelpers } from 'formik';
import withReactContent from 'sweetalert2-react-content';
import { Card, CardBody, Col, Row } from 'reactstrap';

import axios, { AxiosResponse } from 'axios';
import Swal from 'sweetalert2';

import Heading from '@admin/layouts/admin/Heading';
import Loader from '@admin/components/Loader';
import { withRouter, IHasRouter } from '@admin/components/hoc/WithRouter';
import UserForm, { IFormikValues, TForwardedRef } from '@admin/components/users/UserForm';

import { createAuthRequest } from '@admin/utils/api/factories';
import { defaultFormatter } from '@admin/utils/response-formatter/factories';
import User from '@admin/utils/api/models/User';

interface IProps extends IHasRouter<'user'> {

}

const Edit: React.FC<IProps> = ({ router }) => {
    const formikRef = React.createRef<TForwardedRef>();

    const [user, setUser] = React.useState<User>();

    const getUser = React.useCallback(async () => {
        const { params: { user } } = router;

        try {
            const response = await createAuthRequest().get<IUser>(`/users/${user}`);

            setUser(new User(response.data));
        } catch (err) {
            await handleErrorGettingUser(err);
        }
    }, [router.params]);

    const handleErrorGettingUser = React.useCallback(async (err: unknown) => {
        const { navigate } = router;

        const message = defaultFormatter().parse(axios.isAxiosError(err) ? err.response : undefined);

        const result = await withReactContent(Swal).fire({
            icon: 'error',
            title: 'Oops...',
            text: `Unable to retrieve user: ${message}`,
            confirmButtonText: 'Try Again',
            showConfirmButton: true,
            showCancelButton: true
        });

        if (result.isConfirmed) {
            await getUser();
        } else {
            navigate(-1);
        }
    }, []);

    const initialValues = React.useMemo(() => ({
        name: user?.user.name || '',
        email: user?.user.email || '',
        password: '',
        confirm_password: '',
        state: user?.user.state?.code || '',
        country: user?.user.country?.code || '',
        roles: user?.roles || []
    }), [user]);

    React.useEffect(() => {
        getUser();
    }, [router.params.user]);

    const handleSubmit = React.useCallback(async(user: User, { name, email, password, confirm_password, state, country, roles }: IFormikValues, helpers: FormikHelpers<IFormikValues>) => {
        try {
            const response = await createAuthRequest().put<IUser>(`users/${user.user.id}`, {
                name,
                email,
                password,
                password_confirmation: confirm_password,
                state_code: state,
                country_code: country,
                roles
            });

            await handleUpdated(response);
        } catch (e) {
            await handleErrorUpdatingUser(e);
        }
    }, []);

    const handleUpdated = React.useCallback(async (response: AxiosResponse<IUser>) => {
        await withReactContent(Swal).fire({
            icon: 'success',
            title: 'User Updated',
            text: 'The user was successfully updated.',
        });

        setUser(new User(response.data));
    }, []);

    const handleErrorUpdatingUser = React.useCallback(async (err: unknown) => {
        const message = defaultFormatter().parse(axios.isAxiosError(err) ? err.response : undefined);

        await withReactContent(Swal).fire({
            icon: 'error',
            title: 'Oops...',
            text: `An error occurred: ${message}`,
        });
    }, []);

    return (
        <>
            <Helmet>
                <title>Edit User</title>
            </Helmet>

            <Heading title='Edit User' />


            <Row className='justify-content-center'>
                <Col md={8}>
                    <Card>
                        <CardBody>
                            {user !== undefined && (
                                <UserForm
                                    innerRef={formikRef}
                                    fields='edit'
                                    initialValues={initialValues}
                                    buttonContent='Edit User'
                                    onSubmit={(values, helpers) => handleSubmit(user, values, helpers)}
                                />
                            )}
                            {user === undefined && <Loader display={{ type: 'over-element' }} />}
                        </CardBody>
                    </Card>
                </Col>
            </Row>

        </>
    );
}

export default withRouter(Edit);
