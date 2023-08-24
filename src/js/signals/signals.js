/*
	Global event Handler. Modules can now communicate with each othter.

	Based on https://www.codeproject.com/Articles/1119332/JavaScript-Global-Event-System#:~:text=A%20global%20event%20system%20works,handler%20to%20the%20event%20system.
*/
(function (w) {
	w.Signals = w.Signals || {};

	const eventSystem = {
		// event name, callback function will be added to events
        'add': function (name, handler) {
            (this.Events[name])
            ? this.Events[name].Handlers.push(handler)
            : this.Events[name] = { Handlers: [handler] };
        },

		// Event "name" will trigger. args object can be passed. If context is set, the funciton
		// will be called in the defined context (this = context)
		'dispatch': function (name, args, context) {
			if (!context) context = null;
            let i = 0;
            if (this.Events[name]) {
				for (; i < this.Events[name].Handlers.length; i++) {
					this.Events[name].Handlers[i].call(context, args);
                }
            }
        },

		// remove prviously defined event handler
        'remove': function (name, handler) {
            let idx = this.Events[name].Handlers.indexOf(handler);
            if (idx > -1) {
                this.Events[name].Handlers.splice(idx, 1);
            }
        },

        'Events': {}
    };

	w.Signals = eventSystem;
}(window));
