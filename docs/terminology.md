# Terminology

Rivers uses a set of terms revolving around waterways, but avoids more common terms that tend to be overused and can
mean many different things based on context or the whims of the implementor. The common terms automation, workflow,
stream, pipeline, etcetera carry a lot of expectations and assumptions that cause a lot of confusion. 

**Note**: All terms in _italics_ indicate a defined term and not a general term. 

### River
The record that stores a _river map_.

### River Map
A collection of _launches_, _rapids_, _forks_ and _bridges_ that are connected.

### Raft
The thing that can travel down a _river_.

- Eloquent model
- Data object

### River Run
The record for a _raft_ traveling on a _river_.

### Launch
An event that starts a _raft_ on a _river run_ or _portages_ a _raft_.

- Record change
- Webhook called
- Tag applied

### Portage
Moving a _raft_ from any point on the map to a _launch_.

### Rapid
An uninterruptible sequence of _ripples_.

### Ripple
An action. This can be acted on or with a _raft_.

- Notification
- Data modification

### Bridge
Anything that halts a _raft_ from proceeding temporarily.

- Timed delay
- Waiting for data to change
- Waiting for webhook to be called