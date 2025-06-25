# Terminology

Rivers uses a set of terms revolving around waterways, but avoids more common terms that tend to be overused and can
mean many different things based on context or the whims of the implementor. The common terms automation, workflow,
stream, pipeline, etcetera carry a lot of expectations and assumptions that cause a lot of confusion. 


### River
A collection of Sources, Rapids, Forks and Bridges that are connected.

### Raft
The thing traveling down a river.

- Eloquent model
- Data object

### Source
An event that adds a raft to a river or ports a raft to a different point on a river.

- Record change
- Webhook called
- Tag applied

### Ripple
An action that acted on the raft.

- Notification
- Data modification

### Rapid
An uninterruptible sequence of ripples

### Bridge
Anything that halts a raft from proceeding temporarily.

- Timed delay
- Waiting for data to change
- Waiting for webhook to be called