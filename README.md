# Queuing System

This is a PHP implementation of the Redis message passing / queuing
system we are using for Partnermarketing.

To send events to a Stream, use the `EventPublisher`.

To handle events coming in on Streams, you should implement the
`QueueListener` interface (possibly by extending `AbstractQueueListener`
and register it with the `ListenerHandler`.

To load / request Entity values, use the `EntityConsumer` and to act as
a service that generates Entity values, you need to handle requests by
implemtenting a listener on its stream, and save those back to Redis
with the `EntityProvider`.

