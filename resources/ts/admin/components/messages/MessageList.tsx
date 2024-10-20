import React from 'react';
import { Button, Card, CardBody, Col, Form, Input, Label, Row, Table } from 'reactstrap';
import { FaSync } from 'react-icons/fa';

import withReactContent from 'sweetalert2-react-content';
import Swal from 'sweetalert2';
import axios from 'axios';
import { DateTime } from 'luxon';

import MessageRow from './MessageRow';
import MessageModal from './MessageModal';
import WaitToLoad, { IWaitToLoadHandle } from '@admin/components/WaitToLoad';
import Loader from '@admin/components/Loader';
import PaginatedTable, { PaginatedTableHandle } from '@admin/components/paginated-table/PaginatedTable';
import LoadError from '@admin/components/LoadError';

import ContactMessage from '@admin/utils/api/models/ContactMessage';
import awaitModalPrompt from '@admin/utils/modals';
import { createAuthRequest } from '@admin/utils/api/factories';
import { defaultFormatter } from '@admin/utils/response-formatter/factories';

interface IMessageListProps {

}

const MessageList: React.FC<IMessageListProps> = ({ }) => {
    const waitToLoadRef = React.useRef<IWaitToLoadHandle>(null);
    const paginatedTableRef = React.useRef<PaginatedTableHandle>(null);

    const [sortBy, setSortBy] = React.useState('sent_descending');
    const [show, setShow] = React.useState('all');

    const load = React.useCallback(async (link?: string) => {
        const response = await createAuthRequest().get<IPaginateResponseCollection<IContactMessage>>(
            link ?? '/contact-messages',
            {
                sort: sortBy,
                show: show !== 'all' ? show : undefined
            });

        return response.data;
    }, [sortBy, show]);

    const reload = React.useCallback(async () => {
        return paginatedTableRef.current?.reload();
    }, [paginatedTableRef.current]);

    const handleViewClicked = React.useCallback((message: ContactMessage) => {
        awaitModalPrompt(MessageModal, { message });
    }, []);

    const handleMarkUnconfirmedClicked = React.useCallback(async (message: ContactMessage) => {
        try {
            await createAuthRequest().put<IContactMessage>(`/contact-messages/${message.message.uuid}`, {
                confirmed_at: null
            });

            await withReactContent(Swal).fire({
                title: 'Success!',
                text: 'The contact message was marked as unconfirmed.',
                icon: 'success'
            });

        } catch (err) {
            logger.error(err);

            const message = defaultFormatter().parse(axios.isAxiosError(err) ? err.response : undefined)

            await withReactContent(Swal).fire({
                title: 'Oops...',
                text: `An error occurred: ${message}`,
                icon: 'error'
            });
        } finally {
            reload();
        }

    }, [reload]);

    const handleMarkConfirmedClicked = React.useCallback(async (message: ContactMessage) => {
        try {
            await createAuthRequest().put<IContactMessage>(`/contact-messages/${message.message.uuid}`, {
                confirmed_at: DateTime.now().toISO()
            });

            await withReactContent(Swal).fire({
                title: 'Success!',
                text: 'The contact message was marked as confirmed.',
                icon: 'success'
            });

        } catch (err) {
            logger.error(err);

            const message = defaultFormatter().parse(axios.isAxiosError(err) ? err.response : undefined)

            await withReactContent(Swal).fire({
                title: 'Oops...',
                text: `An error occurred: ${message}`,
                icon: 'error'
            });
        } finally {
            reload();
        }

    }, [reload]);

    const handleDeleteClicked = React.useCallback(async (message: ContactMessage) => {
        try {
            const result = await withReactContent(Swal).fire({
                title: 'Are You Sure?',
                text: `This will remove contact message with ID "${message.message.uuid}".`,
                icon: 'question',
                showCancelButton: true
            });

            if (!result.isConfirmed) {
                return;
            }

            const response = await createAuthRequest().delete<Record<'success', string>>(`/contact-messages/${message.message.uuid}`);

            await withReactContent(Swal).fire({
                title: 'Success!',
                text: response.data.success,
                icon: 'success'
            });

        } catch (err) {
            logger.error(err);

            const message = defaultFormatter().parse(axios.isAxiosError(err) ? err.response : undefined)

            await withReactContent(Swal).fire({
                title: 'Oops...',
                text: `An error occurred: ${message}`,
                icon: 'error'
            });
        } finally {
            reload();
        }

    }, [reload]);

    const handleDisplayOptionsFormSubmit = React.useCallback((e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        reload();
    }, [reload]);

    return (
        <>
            <Card>
                <CardBody>
                    <Row>
                        <Col xs={12} className='d-flex flex-column flex-md-row justify-content-between mb-3'>
                            <div className="mb-3 mb-md-0"></div>
                            <div className="text-start text-md-end">
                                <Form className="row row-cols-lg-auto g-3" onSubmit={handleDisplayOptionsFormSubmit}>
                                    <Col xs={12}>
                                        <Label htmlFor="sort" className="col-form-label float-md-start me-1">Sort By: </Label>
                                        <Col className="float-md-start">
                                            <Input type='select' name='sort' id='sort' value={sortBy} onChange={(e) => setSortBy(e.target.value)}>
                                                <option value="from">From</option>
                                                <option value="sent_descending">Sent (Newest to Oldest)</option>
                                                <option value="sent_ascending">Sent (Oldest to Newest)</option>
                                            </Input>
                                        </Col>
                                    </Col>

                                    <Col xs={12}>
                                        <Label htmlFor="show" className="col-form-label float-md-start me-1">Show: </Label>
                                        <Col className="float-md-start">
                                            <Input type='select' name='show' id='show' value={show} onChange={(e) => setShow(e.target.value)}>
                                                <option value="accepted">Accepted</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="unconfirmed">Unconfirmed</option>
                                                <option value="expired">Expired</option>
                                                <option value="all">All</option>
                                            </Input>
                                        </Col>
                                    </Col>

                                    <Col xs={12} className='d-flex flex-column flex-md-row'>
                                        <Button type='submit' color='primary'>
                                            <FaSync /> Update
                                        </Button>
                                    </Col>
                                </Form>
                            </div>
                        </Col>
                        <Col xs={12}>

                            <WaitToLoad
                                ref={waitToLoadRef}
                                loading={<Loader display={{ type: 'over-element' }} />}
                                callback={load}
                            >
                                {(response, err) => (
                                    <>
                                        {response && (
                                            <PaginatedTable
                                                ref={paginatedTableRef}
                                                loader={<Loader display={{ type: 'over-element' }} />}
                                                initialResponse={response}
                                                pullData={load}
                                            >
                                                {(messages, key) => (
                                                    <Table key={key} responsive>
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>From</th>
                                                                <th>Message</th>
                                                                <th>Sent</th>
                                                                <th>Status</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {messages.map((message) => new ContactMessage(message)).map((message, index) => (
                                                                <MessageRow
                                                                    key={index}
                                                                    message={message}
                                                                    onViewClicked={() => handleViewClicked(message)}
                                                                    onMarkUnconfirmedClicked={() => handleMarkUnconfirmedClicked(message)}
                                                                    onMarkConfirmedClicked={() => handleMarkConfirmedClicked(message)}
                                                                    onRemoveClicked={() => handleDeleteClicked(message)}
                                                                />
                                                            ))}
                                                        </tbody>
                                                    </Table>
                                                )}
                                            </PaginatedTable>
                                        )}
                                        {err && (
                                            <LoadError
                                                error={err}
                                                onTryAgainClicked={() => reload()}
                                                onGoBackClicked={() => window.history.back()}
                                            />
                                        )}

                                    </>
                                )}
                            </WaitToLoad>

                        </Col>
                    </Row>

                </CardBody>
            </Card>
        </>
    )
}

export default MessageList;
