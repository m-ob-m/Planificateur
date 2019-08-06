"use strict";

/** A class that handles checkboxes. */
class CheckBox {
	/**
	 * CheckBox constructor.
	 * @param {HTMLInputElement} checkBox An HTML Input Element having its type set to "checkbox".
     * 
     * @return {CheckBox|null} This CheckBox or null if the checkBox parameter is not a valid checkbox.
	 */
	constructor(checkBox){
        this.checkBox = checkBox;
        this.checkBox.addEventListener("change", this.toggle);
        Object.seal(this);
        Object.seal(this.checkBox);
        return this;
	}
	
	/**
	 * Toggles the value of a checkbox between true and false.
	 * 
	 * @param {bool|null} [state=null] The new state of the checkbox. If null, then checkbox is toggled between states.
	 * 
	 * @return {CheckBox} This CheckBox
	 */
	toggle(state = null) {
		if(state === null)
		{
			if(this.getState() === null)
			{
				this.setState(false);
			}
			else
			{
				this.setState(!this.getState());
			}
		}
		else if([true, false].includes(state))
		{
			this.setState(state);
		}

		return this;
	}

	/**
	 * Checks or unchecks the box.
	 * 
	 * @param {bool} state The new state of the CheckBox.
	 * 
	 * @return {CheckBox} This CheckBox
	 */
	setState(state)
	{
		if(this.checkBox.checked !== state)
		{
			this.checkBox.checked = state;
		}
		return this;
	}

	/**
	 * Returns the state of the checkbox.
	 * 
	 * @return {bool} The state of the Checkbox
	 */
	getState()
	{
		return this.checkBox.checked;
	}
}