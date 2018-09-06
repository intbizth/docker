require('dotenv').config();
const Promise = require('es6-promise').Promise;
const AmqpLib = require('amqp-connection-manager');
const Logger = require('./logger');
const Queues = process.env['AMQP_QUEUES'].split(',');
const Services = {
    worker: require('./worker'),
    socket: require('./socket')
};

const Connection = AmqpLib.connect(process.env['AMQP_HOST']);

Connection.on('connect', function () {
    Logger.info('AMQP Connected!');
});

Connection.on('disconnect', function (params) {
    Logger.info('AMQP Disconnected.');
    console.log(params.err);
});

for (let i in Queues) {
    Connection.createChannel({
        json: true,
        setup: (function (queue) {
            return function (channel) {
                return Promise.all([
                    channel.assertQueue(queue, { durable: true }),
                    channel.prefetch(1),
                    channel.consume(queue, function (msg) {
                        let data = {};
                        try {
                            data = JSON.parse(msg.content.toString());
                        } catch (e) {
                            Logger.error(e.message);
                            Logger.error("MSG: " + msg.content.toString());
                        }
                        try {
                            const extras = data['extras'] || {};
                            let hasService = false;

                            for (let n in extras) {
                                if (Services.hasOwnProperty(n)) {
                                    Services[n].call(null, channel, msg, data, extras[n]);
                                    hasService = true;
                                }
                            }

                            if (!hasService) {
                                Logger.warn('ACK with no service to handle.');
                                channel.ack(msg);
                            }
                        } catch (e) {
                            Logger.error('ACK with error: ' + e.message);
                            channel.ack(msg);
                        }
                    })
                ]);
            }
        })(Queues[i])
    });
}
